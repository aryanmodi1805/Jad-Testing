<?php

namespace App\Http\Controllers;

use App\Enums\VerifyType;
use App\Exceptions\CooldownOtpException;
use App\Http\Resources\CountryResource;
use App\Models\Country;
use App\Models\Customer;
use App\Models\OtpCode;
use App\Models\Seller;
use App\Settings\AppSettings;
use App\Traits\Auth\InteractWIthOTP;
use Auth;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Exception;
use Filament\Facades\Filament;
use Filament\Notifications\Auth\ResetPassword;
use Filament\Notifications\Auth\ResetPassword as ResetPasswordNotification;
use Hash;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Lang;

class AppAuthController extends Controller
{
    use WithRateLimiting;
    use InteractWIthOTP;

    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['login', 'register', 'forgot','loginByPhone', 'requestLoginOtp','verifyLoginOtp']);
    }

    public function logout()
    {
        Auth::user()->tokens()->delete();

        return $this->ApiResponseFormatted(200, null, 'Successfully logged out');
    }

    public function requestLoginOtp(Request $request, AppSettings $settings)
    {
        $validateUser = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);

        if ($validateUser->fails()) {
            return $this->ApiResponseFormatted(422, null, Lang::get('api.validation_error'), $settings, $request);
        }

        if (!Limit::perMinutes(5, 5)->by($request->phone . $request->ip())) {
            return $this->ApiResponseFormatted(429, null, Lang::get('api.too_many_requests'), $settings, $request);
        }

        // Test account bypass: allow test phone to proceed even if account doesn't exist
        // DO NOT send actual OTP to this number
        $normalizedPhone = preg_replace('/[^\d]/', '', $request->phone);
        $testPhone = '966539649452';
        
        [$customer, $seller] = $this->resolveAccountsByPhone($request->phone);

        // Check if the account is blocked
        $primaryAccount = $customer ?? $seller;
        if ($primaryAccount != null && $primaryAccount->blocked) {
            return $this->ApiResponseFormatted(403, null, Lang::get('api.account_blocked'), $settings, $request);
        }

        if ($customer == null && $seller == null) {
            // Allow test account to proceed without database account
            if ($normalizedPhone === $testPhone) {
                return $this->ApiResponseFormatted(200, [
                    'priority' => 'customer',
                    'verification_status' => [
                        'expires_at' => now()->addMinutes(5),
                        'cooldown' => false,
                        'cooldown_for' => null,
                        'phone_verified' => false,
                        'email_verified' => false,
                        'phone' => $request->phone,
                    ],
                ], Lang::get('api.otp_sent'), $settings, $request);
            }
            return $this->ApiResponseFormatted(404, null, Lang::get('api.account_not_found'), $settings, $request);
        }

        $primaryAccount = $customer ?? $seller;

        // Skip OTP sending for test account
        if ($normalizedPhone === $testPhone) {
            return $this->ApiResponseFormatted(200, [
                'priority' => $customer != null ? 'customer' : 'seller',
                'verification_status' => [
                    'expires_at' => now()->addMinutes(5),
                    'cooldown' => false,
                    'cooldown_for' => null,
                    'phone_verified' => $primaryAccount->phone_verified_at != null,
                    'email_verified' => $primaryAccount->email_verified_at != null,
                    'phone' => $request->phone,
                ],
            ], Lang::get('api.otp_sent'), $settings, $request);
        }

        try {
            $verificationStatus = $this->sendAndStatus($primaryAccount);
        } catch (\Exception $e) {
            return $this->ApiResponseFormatted(422, null, $e->getMessage(), $settings, $request);
        }

        return $this->ApiResponseFormatted(200, [
            'priority' => $customer != null ? 'customer' : 'seller',
            'verification_status' => $verificationStatus,
        ], Lang::get('api.otp_sent'), $settings, $request);
    }

    public function verifyLoginOtp(Request $request, AppSettings $settings)
    {
        $validateUser = Validator::make($request->all(), [
            'phone' => 'required|string',
            'code' => 'required|string',
            'device_token' => 'required|string',
        ]);

        if ($validateUser->fails()) {
            return $this->ApiResponseFormatted(422, null, Lang::get('api.validation_error'), $settings, $request);
        }

        if (!Limit::perMinutes(5, 5)->by($request->phone . $request->ip())) {
            return $this->ApiResponseFormatted(429, null, Lang::get('api.too_many_requests'), $settings, $request);
        }

        // Test account bypass: phone 966539649452 with code 180504
        // Normalize phone number (remove + and spaces)
        $normalizedPhone = preg_replace('/[^\d]/', '', $request->phone);
        $testPhone = '966539649452';
        $testCode = '180504';
        
        [$customer, $seller] = $this->resolveAccountsByPhone($request->phone);

        // Check if the account is blocked
        $primaryAccount = $customer ?? $seller;
        if ($primaryAccount != null && $primaryAccount->blocked) {
            return $this->ApiResponseFormatted(403, null, Lang::get('api.account_blocked'), $settings, $request);
        }

        if ($customer == null && $seller == null) {
            // Allow test account to proceed with bypass code
            if ($normalizedPhone === $testPhone && $request->code === $testCode) {
                // Return mock successful login for test account
                return $this->ApiResponseFormatted(200, [
                    'priority' => 'customer',
                    'customer' => [
                        'token' => 'test-token-' . time(),
                        'verification_status' => [
                            'expires_at' => null,
                            'cooldown' => false,
                            'cooldown_for' => null,
                            'phone_verified' => true,
                            'email_verified' => false,
                            'phone' => $request->phone,
                        ]
                    ]
                ], Lang::get('api.otp_verified'), $settings, $request);
            }
            return $this->ApiResponseFormatted(404, null, Lang::get('api.account_not_found'), $settings, $request);
        }

        $account = $customer ?? $seller;
        
        if ($normalizedPhone === $testPhone && $request->code === $testCode) {
            // Bypass OTP verification for test account
            $account->update(['phone_verified_at' => now()]);
        } else {
            try {
                $this->verifyCode($account, $request->code);
            } catch (\Exception $e) {
                    return $this->ApiResponseFormatted(422, null, $e->getMessage(), $settings, $request);
            }
        }

        $response['priority'] = $customer != null ? 'customer' : 'seller';

        if ($customer != null) {
            $this->deviceToken($customer, $request->device_token);
            $response['customer'] = [
                'token' => $customer->createToken($request->device_token)->plainTextToken,
                'verification_status' => $this->verificationStatus($customer)
            ];
        }

        if ($seller != null) {
            $this->deviceToken($seller, $request->device_token);
            $response['seller'] = [
                'token' => $seller->createToken($request->device_token)->plainTextToken,
                'verification_status' => $this->verificationStatus($seller)
            ];
        }

        return $this->ApiResponseFormatted(200, $response, Lang::get('api.otp_verified'), $settings, $request);
    }

    public function loginByPhone(Request $request, AppSettings $settings)
    {
        $validateUser = Validator::make($request->all(), [
            'phone' => 'required|string',
            'password' => 'required',
            'device_token' => 'required|string',
        ]);

        if ($validateUser->fails()) {
            return $this->ApiResponseFormatted(422, null, Lang::get('api.validation_error'), $settings, $request);
        }

        if (!Limit::perMinutes(5, 5)->by($request->phone . $request->ip())) {
            return $this->ApiResponseFormatted(429, null, Lang::get('api.too_many_requests'), $settings, $request);
        }

        // Clean and normalize phone number for searching
        $phone = $request->phone;
//        $cleanPhone = preg_replace('/[^\d+]/', '', $phone);

        // Try to find customer/seller with exact match first, then with normalized number
        $customer = Customer::where('phone', $phone)->first();

        $seller = Seller::where('phone', $phone)->first();


        $checkCustomer = $customer != null && Hash::check($request->password, $customer->password);
        $checkSeller = $seller != null && Hash::check($request->password, $seller->password);

        if (!$checkCustomer && !$checkSeller) {
            return $this->ApiResponseFormatted(401, null, Lang::get('api.invalid_credentials'), $settings, $request);
        }

        // Check if the account is blocked
        $primaryAccount = $checkCustomer ? $customer : $seller;
        if ($primaryAccount->blocked) {
            return $this->ApiResponseFormatted(403, null, Lang::get('api.account_blocked'), $settings, $request);
        }

        $response['priority'] = $checkCustomer ? 'customer' : 'seller';

        if ($customer == null && $seller->customer_id != null) {
            $customer = $seller->associatedAccount;
        } elseif ($seller == null && $customer->seller_id != null) {
            $seller = $customer->associatedAccount;
        }

        if ($customer != null) {
            $this->deviceToken($customer, $request->device_token);

            $normalizedPhone = preg_replace('/[^\d]/', '', $customer->phone);
            $testPhone = '966539649452';

            if ($response['priority'] == 'customer' && $normalizedPhone !== $testPhone) {
                $otpArray = $this->sendAndStatus($customer);
            } else {
                $otpArray = $this->verificationStatus($customer);
            }

            $response['customer'] = [
                'token' => $customer->createToken($request->device_token)->plainTextToken,
                'verification_status' => $otpArray
            ];
        }

        if ($seller != null) {
            $this->deviceToken($seller, $request->device_token);

            $normalizedPhone = preg_replace('/[^\d]/', '', $seller->phone);
            $testPhone = '966539649452';

            if ($response['priority'] == 'seller' && $normalizedPhone !== $testPhone) {
                $otpArray = $this->sendAndStatus($seller);
            } else {
                $otpArray = $this->verificationStatus($seller);
            }

            $response['seller'] = [
                'token' => $seller->createToken($request->device_token)->plainTextToken,
                'verification_status' => $otpArray
            ];
        }

        return $this->ApiResponseFormatted(200, $response, Lang::get('api.success'), $settings, $request);
    }

    public function login(Request $request, AppSettings $settings)
    {
        $validateUser = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'device_token' => 'required|string',
        ]);

        if ($validateUser->fails()) {
            return $this->ApiResponseFormatted(422, null, Lang::get('api.validation_error'), $settings, $request);
        }

        if (!Limit::perMinutes(5, 5)->by($request->email . $request->ip())) {
            return $this->ApiResponseFormatted(429, null, Lang::get('api.too_many_requests'), $settings, $request);
        }

        $customer = Customer::where('email', $request->email)->first();
        $seller = Seller::where('email', $request->email)->first();

        $checkCustomer = $customer != null && Hash::check($request->password, $customer->password);
        $checkSeller = $seller != null && Hash::check($request->password, $seller->password);

        if (!$checkCustomer && !$checkSeller) {
            return $this->ApiResponseFormatted(401, null, Lang::get('api.invalid_credentials'), $settings, $request);
        }

        // Check if the account is blocked
        $primaryAccount = $checkCustomer ? $customer : $seller;
        if ($primaryAccount->blocked) {
            return $this->ApiResponseFormatted(403, null, Lang::get('api.account_blocked'), $settings, $request);
        }

        $response['priority'] = $checkCustomer ? 'customer' : 'seller';

        if ($customer == null && $seller->customer_id != null) {
            $customer = $seller->associatedAccount;
        } elseif ($seller == null && $customer->seller_id != null) {
            $seller = $customer->associatedAccount;
        }

        if ($customer != null) {
            $this->deviceToken($customer, $request->device_token);

            $normalizedPhone = preg_replace('/[^\d]/', '', $customer->phone);
            $testPhone = '966539649452';

            if ($response['priority'] == 'customer' && $normalizedPhone !== $testPhone) {
                $otpArray = $this->sendAndStatus($customer);
            } else {
                $otpArray = $this->verificationStatus($customer);
            }

            $response['customer'] = [
                'token' => $customer->createToken($request->device_token)->plainTextToken,
                'verification_status' => $otpArray
            ];
        }

        if ($seller != null) {
            $this->deviceToken($seller, $request->device_token);

            $normalizedPhone = preg_replace('/[^\d]/', '', $seller->phone);
            $testPhone = '966539649452';

            if ($response['priority'] == 'seller' && $normalizedPhone !== $testPhone) {
                $otpArray = $this->sendAndStatus($seller);
            } else {
                $otpArray = $this->verificationStatus($seller);
            }

            $response['seller'] = [
                'token' => $seller->createToken($request->device_token)->plainTextToken,
                'verification_status' => $otpArray
            ];

        }

        return $this->ApiResponseFormatted(200, $response, Lang::get('api.success'), $settings, $request);
    }

    public function addSubdomainToHost(Request $request)
    {
        $request->headers->set('host', 'sa.evantto.test');

    }

    public function forgot(Request $request, AppSettings $settings)
    {
        $validateUser = Validator::make($request->all(), [
            'email' => 'required|email',
            'type' => 'required|string',

        ]);

        $countryId = $this->getCountryId($request);
        $country = Country::query()->where('id', $countryId)->first();

        if ($country == null) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.country_not_found'), request: $request);
        }


        //Get Host and Add Subdomain
        $host = $request->getHost();
        $subdomain = $country->slug;

        URL::forceRootUrl("https://$subdomain.$host");


        if ($validateUser->fails()) {
            return $this->ApiResponseFormatted(422, null, Lang::get('api.validation_error'), $settings, $request);
        }

        if (!Limit::perMinutes(5, 5)->by($request->email . $request->ip())) {
            return $this->ApiResponseFormatted(429, null, Lang::get('api.too_many_requests'), $settings, $request);
        }

        $user = null;

        if ($request->type == 'customer') {
            $user = Customer::where('email', $request->email)->first();
        } elseif ($request->type == 'seller') {
            $user = Seller::where('email', $request->email)->first();
        }
        //No need to notify the user if the email is not found
        if ($user == null) {
            return $this->ApiResponseFormatted(200, Lang::get('api.success'), settings: $settings, request: $request);
        }

        Filament::setCurrentPanel(Filament::getPanel($request->type));

        try {
            $status = Password::broker(Filament::getAuthPasswordBroker())->sendResetLink(
                [
                    'email' => $request->email,
                ],
                function (CanResetPassword $user, string $token): void {
                    if (!method_exists($user, 'notify')) {
                        $userClass = $user::class;

                        throw new Exception("Model [{$userClass}] does not have a [notify()] method.");
                    }

                    $notification = new ResetPasswordNotification($token);
                    $notification->url = Filament::getResetPasswordUrl($token, $user);

                    $user->notify($notification);
                },
            );
        } catch (\Exception $e) {
            return $this->ApiResponseFormatted(500, null, $e->getMessage(), $settings, $request);
        }

        return $this->ApiResponseFormatted(200, $status, Lang::get('api.success'), settings: $settings, request: $request);
    }

    public function register(Request $request, AppSettings $settings)
    {
        $validateData = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'required|string',
            'country_code' => 'required|string',
            'device_token' => 'required|string',
            'is_customer' => 'required|bool',
        ]);


        if ($validateData->fails()) {
            return $this->ApiResponseFormatted(422, null, Lang::get('api.validation_error'), $settings, $request);
        }

        if ($request->is_customer) {
            $model = Customer::class;
        } else {
            $model = Seller::class;
        }

        $existsQuery = $model::where('phone', $request->phone);

        if ($request->filled('email')) {
            $existsQuery->orWhere('email', $request->email);
        }

        $country = Country::where('code', $request->country_code)->first();

        if ($country == null) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.country_not_found'), $settings, $request);
        }

            if ($existsQuery->exists()) {
            $account = $existsQuery->first();

            // Check if the account is blocked
            if ($account->blocked) {
                return $this->ApiResponseFormatted(403, null, Lang::get('api.account_blocked'), $settings, $request);
            }
            
            // Build update data
            $updateData = [
                'name' => $request->name,
                'email' => $request->filled('email') ? $request->email : $account->email,
                'country_id' => $country->id,
            ];

            // Handle Avatar Upload (support both 'avatar' and 'profile_photo')
            if ($request->hasFile('avatar')) {
                $updateData['avatar_url'] = $request->file('avatar')->store('avatars', 'public');
            } elseif ($request->hasFile('profile_photo')) {
                $updateData['avatar_url'] = $request->file('profile_photo')->store('avatars', 'public');
            }

            // Update existing account details
            $account->update($updateData);
            
            // Ensure device token is added
            $this->deviceToken($account, $request->device_token);

            return $this->ApiResponseFormatted(200, [
                'token' => $account->createToken($request->device_token)->plainTextToken,
                'verification_status' => $this->sendAndStatus($account)
            ], Lang::get('api.success'), $settings, $request);
        }

        $account = $model::create([
            'name' => $request->name,
            'email' => $request->filled('email') ? $request->email : null,
            'password' => Hash::make(Str::random(16)),
            'phone' => $request->phone,
            'country_id' => $country->id,
            'tokens' => [$request->device_token]
        ]);


        return $this->ApiResponseFormatted(200, [
            'token' => $account->createToken($request->device_token)->plainTextToken,
            'verification_status' => $this->sendAndStatus($account)
        ], Lang::get('api.success'), $settings, $request);
    }

    public function verify(Request $request, AppSettings $settings)
    {

        $validateData = Validator::make($request->all(), [
            'code' => 'required|string',
            'type' => 'required|int',
        ]);


        if ($validateData->fails()) {
            return $this->ApiResponseFormatted(422, null, Lang::get('api.validation_error'), $settings, $request);
        }

        if (VerifyType::from($request->type) == VerifyType::OTP) {
            try {
                $this->verifyCode($request->user(), $request->code);
                return $this->ApiResponseFormatted(200, true, Lang::get('api.success'), $settings, $request);
            } catch (\Exception $e) {
                return $this->ApiResponseFormatted(422, null, $e->getMessage(), $settings, $request);

            }
        } else {
            return $this->ApiResponseFormatted(404, '', Lang::get('api.country_not_found'), $settings, $request);

        }


    }

    public function resend(Request $request, AppSettings $settings)
    {
        try {
            return $this->ApiResponseFormatted(200, $this->sendAndStatus($request->user()), Lang::get('api.success'), $settings, $request);
        } catch (\Exception $e) {
            return $this->ApiResponseFormatted(422, null, $e->getMessage(), $settings, $request);

        }
    }

    public function verifyStatus(Request $request, AppSettings $settings)
    {
        try {
            return $this->ApiResponseFormatted(200, $this->verificationStatus($request->user()), Lang::get('api.success'), $settings, $request);
        } catch (\Exception $e) {
            return $this->ApiResponseFormatted(422, null, $e->getMessage(), $settings, $request);

        }
    }

    public function changePhone(Request $request, AppSettings $settings)
    {
        $validateData = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);


        if ($validateData->fails()) {
            return $this->ApiResponseFormatted(422, null, Lang::get('api.validation_error'), $settings, $request);
        }

        $account = $request->user();

        $account->update([
            'phone' => $request->phone,
        ]);

        return $this->ApiResponseFormatted(200, $this->sendAndStatus($account), Lang::get('api.success'), $settings, $request);

    }

    public function sendAndStatus(Customer|Seller $account): array
    {
        try {
            $this->sendOtp($account);
        } catch (CooldownOtpException $e) {
        }

        return $this->verificationStatus($account);

    }

    public function verificationStatus(Customer|Seller $account): array
    {
        $otpRecord = $this->getRecordByAccount($account);

        $cooldown = $otpRecord != null && $this->checkCooldown($otpRecord);

        if ($otpRecord != null && !$cooldown && $otpRecord?->expires_at < now()) {
            try {
                $this->sendOtp($account);
            } catch (CooldownOtpException $e) {
            } finally {
                $otpRecord->refresh();
            }
        }

        return [
            "expires_at" => $otpRecord != null ? $otpRecord->expires_at : null,
            "cooldown" => $cooldown,
            "cooldown_for" => $otpRecord != null ? $otpRecord->cooldown_end->diffForHumans() : null,
            'phone_verified' => $account->phone_verified_at != null,
            'email_verified' => $account->email_verified_at != null,
            'phone' => $account->phone,
        ];

    }

    protected function resolveAccountsByPhone(string $phone): array
    {
        // Try exact match first
        $customer = Customer::where('phone', $phone)->first();
        $seller = Seller::where('phone', $phone)->first();
        
        // If not found, try with + prefix (common format)
        if (!$customer && !$seller) {
            $phoneWithPlus = (strpos($phone, '+') === 0) ? $phone : '+' . $phone;
            $phoneWithoutPlus = (strpos($phone, '+') === 0) ? substr($phone, 1) : $phone;
            
            $customer = Customer::where('phone', $phoneWithPlus)
                ->orWhere('phone', $phoneWithoutPlus)
                ->first();
            $seller = Seller::where('phone', $phoneWithPlus)
                ->orWhere('phone', $phoneWithoutPlus)
                ->first();
        }

        if ($customer == null && $seller?->customer_id != null) {
            $customer = $seller->associatedAccount;
        } elseif ($seller == null && $customer?->seller_id != null) {
            $seller = $customer->associatedAccount;
        }

        return [$customer, $seller];
    }

    public function deviceToken($account, $token)
    {
        $deviceTokens = $account->tokens ?? [];
        if (!in_array($token, $deviceTokens)) {
            $deviceTokens[] = $token;
            $account->update([
                'tokens' => $deviceTokens
            ]);
        }
    }

    public function changePassword(Request $request, AppSettings $settings)
    {
        $validate = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|confirmed|string|min:8',
        ]);

        if ($validate->fails()) {
            return $this->ApiResponseFormatted(422, null, Lang::get('api.validation_error'), $settings, $request);
        }

        $account = $request->user();

        if (!Hash::check($request->old_password, $account->password)) {
            return $this->ApiResponseFormatted(401, null, Lang::get('api.invalid_credentials'), $settings, $request);
        }

        $account->update([
            'password' => Hash::make($request->new_password),
        ]);

        return $this->ApiResponseFormatted(200, null, Lang::get('api.success'), $settings, $request);
    }

    public function changeProfile(Request $request, AppSettings $settings)
    {
        $account = $request->user();
        if ($account instanceof Customer) {

            $validate = Validator::make($request->all(), [
                'phone' => 'required|string',
                'name' => 'required|string',
            ]);

            if ($validate->fails()) {
                return $this->ApiResponseFormatted(422, null, Lang::get('api.validation_error'), $settings, $request);
            }


            $phoneChanged = $account->phone != $request->phone;

            $account->update([
                'phone' => $request->phone,
                'name' => $request->name,
                'phone_verified_at' => $phoneChanged ? null : $account->phone_verified_at,
            ]);

            if ($phoneChanged) {
                $this->sendAndStatus($account);
            }
        }elseif ($account instanceof Seller) {
            $validate = Validator::make($request->all(), [
                'phone' => 'required|string',
                'name' => 'required|string',
                'website' => 'string|nullable',
                'bio' => 'string',
                'socialMedia' => 'array',
            ]);



            if ($validate->fails()) {
                return $this->ApiResponseFormatted(422, null, Lang::get('api.validation_error'), $settings, $request);
            }

            $account->socialMedia()->whereNotIn('id', array_column($request->socialMedia, 'id'))->delete();
            foreach ($request->socialMedia as $value) {
                if($value['id'] == null){
                    $account->socialMedia()->create($value);
                }else{
                    $id = $value['id'];
                    unset($value['id']);
                    $account->socialMedia()->where('id', $id)->update($value);

                }
            }
            $phoneChanged = $account->phone != $request->phone;

            $account->update([
                'phone' => $request->phone,
                'company_name' => $request->name,
                'website' => $request->website,
                'company_description' => $request->bio,
                'phone_verified_at' => $phoneChanged ? null : $account->phone_verified_at,
            ]);

            if ($phoneChanged) {
                $this->sendAndStatus($account);
            }
        }
        return $this->ApiResponseFormatted(200, null, Lang::get('api.success'), $settings, $request);
    }

    public function changeAvatar(Request $request, AppSettings $settings)
    {
        $account = $request->user();

        if (isset($request->allFiles()['files'])) {
            $requestFiles = $request->allFiles()['files'];
            if (isset($requestFiles[0])) {
                $requestFile = $requestFiles[0];

                $account->update([
                    'avatar_url' => $requestFile->store('avatars', 'public')
                ]);

            }

        }

        return $this->ApiResponseFormatted(200, null, Lang::get('api.success'), $settings, $request);
    }

    public function deleteCustomerAccount(Request $request, AppSettings $settings)
    {
        $account = $request->user();

        if (!($account instanceof Customer)) {
            return $this->ApiResponseFormatted(401, null, Lang::get('api.unauthorized'), $settings, $request);
        }

        try {
            // Delete all tokens
            $account->tokens()->delete();

            // Soft delete the customer account
            $account->delete();

            return $this->ApiResponseFormatted(200, null, Lang::get('api.account_deleted_successfully'), $settings, $request);
        } catch (\Exception $e) {
            return $this->ApiResponseFormatted(500, null, Lang::get('api.server_error'), $settings, $request);
        }
    }

    public function deleteSellerAccount(Request $request, AppSettings $settings)
    {
        $account = $request->user();

        if (!($account instanceof Seller)) {
            return $this->ApiResponseFormatted(401, null, Lang::get('api.unauthorized'), $settings, $request);
        }

        try {
            // Delete all tokens
            $account->tokens()->delete();

            // Soft delete the seller account
            $account->delete();

            return $this->ApiResponseFormatted(200, null, Lang::get('api.account_deleted_successfully'), $settings, $request);
        } catch (\Exception $e) {
            return $this->ApiResponseFormatted(500, null, Lang::get('api.server_error'), $settings, $request);
        }
    }


}
