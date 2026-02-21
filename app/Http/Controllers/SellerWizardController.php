<?php

namespace App\Http\Controllers;

use App\Exceptions\CooldownOtpException;
use App\Models\Country;
use App\Models\Seller;
use App\Models\SellerServiceLocation;
use App\Settings\AppSettings;
use App\Traits\Auth\InteractWIthOTP;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Lang;

class SellerWizardController extends Controller
{
    use InteractWIthOTP;

    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['registerSellerWizard', 'testRequest']);
    }

    /**
     * Complete seller registration through wizard
     * IF seller already exists → reuse account (NO DUPLICATES)
     */
    public function registerSellerWizard(Request $request, AppSettings $settings)
    {
        $validateData = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'required|string',
            'country_code' => 'required|string',
            'device_token' => 'required|string',

            'services' => 'required|array|min:1',
            'services.*' => 'string|exists:services,id',

            'is_nationwide' => 'required|bool',
            'location_name' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'location_range' => 'nullable|integer|min:1|max:500',

            'company_name' => 'nullable|string|max:255',
            'company_description' => 'nullable|string|max:1000',
            'years_in_business' => 'nullable|integer|min:0|max:50',

            'identification_document' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ]);

        if ($validateData->fails()) {
            return $this->ApiResponseFormatted(
                422,
                null,
                Lang::get('api.validation_error'),
                $settings,
                $request,
                $validateData->errors()
            );
        }

        $isNationwide = filter_var($request->is_nationwide, FILTER_VALIDATE_BOOLEAN);

        if (!$isNationwide) {
            Validator::make($request->all(), [
                'location_name' => 'required|string|max:255',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'location_range' => 'required|integer|min:1|max:500',
            ])->validate();
        }

        $country = Country::where('code', $request->country_code)->first();
        if (!$country) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.country_not_found'), $settings, $request);
        }

        /**
         * 🔁 REUSE EXISTING SELLER (NO DUPLICATES)
         */
        $seller = Seller::where('phone', $request->phone)
            ->when($request->filled('email'), fn ($q) =>
                $q->orWhere('email', $request->email)
            )
            ->first();

        if ($seller) {
            // Check if the seller is blocked
            if ($seller->blocked) {
                return $this->ApiResponseFormatted(403, null, Lang::get('api.account_blocked'), $settings, $request);
            }

            $tokens = $seller->tokens ?? [];
            if (!in_array($request->device_token, $tokens)) {
                $tokens[] = $request->device_token;
            }

            // Update Basic Info
            $sellerData = [
                'name' => $request->name,
                'email' => $request->filled('email') ? $request->email : $seller->email,
                'country_id' => $country->id ?? $seller->country_id,
                'tokens' => $tokens,
                'company_name' => $request->company_name,
                'company_description' => $request->company_description,
                'years_in_business' => $request->years_in_business,
            ];

            // Handle Document Update
            if ($request->hasFile('identification_document')) {
                $path = $request->file('identification_document')
                    ->store('identification_documents', 'public');

                $sellerData['identification_document_url'] = $path;
                $sellerData['identification_document_status'] = 'pending';
            }

            // Handle Avatar Upload (support both 'avatar' and 'profile_photo')
            if ($request->hasFile('avatar')) {
                $sellerData['avatar_url'] = $request->file('avatar')->store('avatars', 'public');
            } elseif ($request->hasFile('profile_photo')) {
                $sellerData['avatar_url'] = $request->file('profile_photo')->store('avatars', 'public');
            }

            $seller->update($sellerData);

            // Update Services and Locations
            $seller->services()->sync($request->services);

            // Delete old locations to replace with new one (wizard assumes single primary location setup usually)
            $seller->sellerLocations()->delete();

            $location = $seller->sellerLocations()->create([
                'name' => $isNationwide ? 'Nationwide Coverage' : $request->location_name,
                'location_name' => $isNationwide ? 'nationwide' : $request->location_name,
                'is_nationwide' => $isNationwide,
                'latitude' => $isNationwide ? null : $request->latitude,
                'longitude' => $isNationwide ? null : $request->longitude,
                'location_range' => $isNationwide ? null : $request->location_range,
            ]);

            foreach (
                $seller->sellerServices()->whereIn('service_id', $request->services)->get()
                as $service
            ) {
                SellerServiceLocation::create([
                    'seller_service_id' => $service->id,
                    'seller_location_id' => $location->id,
                ]);
            }

            return $this->ApiResponseFormatted(200, [
                'already_registered' => false, // Treat as new registration for frontend flow
                'priority' => 'seller',
                'seller' => $this->sellerPayload($seller, $request->device_token, true),
            ], Lang::get('api.seller_registered_successfully'), $settings, $request);
        }

        /**
         * 🆕 CREATE NEW SELLER
         */
        DB::beginTransaction();
        try {
            $seller = Seller::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make(Str::random(16)),
                'phone' => $request->phone,
                'country_id' => $country->id,
                'tokens' => [$request->device_token],
                'company_name' => $request->company_name,
                'company_description' => $request->company_description,
                'years_in_business' => $request->years_in_business,
            ]);

            if ($request->hasFile('identification_document')) {
                $path = $request->file('identification_document')
                    ->store('identification_documents', 'public');

                $seller->update([
                    'identification_document_url' => $path,
                    'identification_document_status' => 'pending',
                ]);
            }

            $seller->services()->sync($request->services);

            $location = $seller->sellerLocations()->create([
                'name' => $isNationwide ? 'Nationwide Coverage' : $request->location_name,
                'location_name' => $isNationwide ? 'nationwide' : $request->location_name,
                'is_nationwide' => $isNationwide,
                'latitude' => $isNationwide ? null : $request->latitude,
                'longitude' => $isNationwide ? null : $request->longitude,
                'location_range' => $isNationwide ? null : $request->location_range,
            ]);

            foreach (
                $seller->sellerServices()->whereIn('service_id', $request->services)->get()
                as $service
            ) {
                SellerServiceLocation::create([
                    'seller_service_id' => $service->id,
                    'seller_location_id' => $location->id,
                ]);
            }

            DB::commit();

            return $this->ApiResponseFormatted(200, [
                'already_registered' => false,
                'priority' => 'seller',
                'seller' => $this->sellerPayload($seller, $request->device_token, true),
            ], Lang::get('api.seller_registered_successfully'), $settings, $request);

        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->ApiResponseFormatted(500, null, $e->getMessage(), $settings, $request);
        }
    }

    /**
     * Unified seller payload (NULL-SAFE FOR FLUTTER)
     */
    private function sellerPayload(Seller $seller, string $deviceToken, bool $sendOtp = false): array
    {
        return [
            'token' => $seller->createToken($deviceToken)->plainTextToken,
            'name' => $seller->name ?? '',
            'email' => $seller->email ?? '',
            'phone' => $seller->phone ?? '',
            'company_name' => $seller->company_name ?? '',
            'verification_status' => $sendOtp
                ? $this->sendAndStatus($seller)
                : $this->verificationStatus($seller),
        ];
    }

    /**
     * OTP helpers (NULL-SAFE)
     */
    public function sendAndStatus(Seller $account): array
    {
        try {
            $this->sendOtp($account);
        } catch (CooldownOtpException $e) {}

        return $this->verificationStatus($account);
    }

    public function verificationStatus(Seller $account): array
    {
        $otpRecord = $this->getRecordByAccount($account);
        $cooldown = $otpRecord && $this->checkCooldown($otpRecord);

        return [
            'expires_at' => $otpRecord?->expires_at?->toISOString() ?? '',
            'cooldown' => (bool) $cooldown,
            'cooldown_for' => $otpRecord?->cooldown_end?->diffForHumans() ?? '',
            'phone_verified' => (bool) $account->phone_verified_at,
            'email_verified' => (bool) $account->email_verified_at,
            'phone' => $account->phone ?? '',
            'email' => $account->email ?? '',
        ];
    }

    public function testRequest(Request $request, AppSettings $settings)
    {
        return $this->ApiResponseFormatted(200, $request->all(), 'Test successful', $settings, $request);
    }
}
