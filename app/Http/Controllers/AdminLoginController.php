<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Seller;
use App\Settings\AppSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Lang;

class AdminLoginController extends Controller
{
    public function adminLogin(Request $request, AppSettings $settings)
    {
        $validateUser = Validator::make($request->all(), [
            'phone' => 'required|string',
            'master_key' => 'required|string|size:6',
            'device_token' => 'required|string',
        ]);

        if ($validateUser->fails()) {
            return $this->ApiResponseFormatted(422, null, Lang::get('api.validation_error'), $settings, $request);
        }

        // Get the admin master key from settings
        $adminMasterKey = DB::table('settings')
            ->where('group', 'app')
            ->where('name', 'admin_master_key')
            ->value('admin_master_key');

        if (!$adminMasterKey) {
            return $this->ApiResponseFormatted(401, null, 'Admin master key not configured', $settings, $request);
        }

        // Verify the master key
        if (!Hash::check($request->master_key, $adminMasterKey)) {
            return $this->ApiResponseFormatted(401, null, 'Invalid master key', $settings, $request);
        }

        // Find the user by phone
        $customer = Customer::where('phone', $request->phone)->first();
        $seller = Seller::where('phone', $request->phone)->first();

        // Try with + prefix variations if not found
        if (!$customer && !$seller) {
            $phoneWithPlus = (strpos($request->phone, '+') === 0) ? $request->phone : '+' . $request->phone;
            $phoneWithoutPlus = (strpos($request->phone, '+') === 0) ? substr($request->phone, 1) : $request->phone;
            
            $customer = Customer::where('phone', $phoneWithPlus)
                ->orWhere('phone', $phoneWithoutPlus)
                ->first();
            $seller = Seller::where('phone', $phoneWithPlus)
                ->orWhere('phone', $phoneWithoutPlus)
                ->first();
        }

        if (!$customer && !$seller) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.account_not_found'), $settings, $request);
        }

        // Get associated accounts
        if ($customer == null && $seller?->customer_id != null) {
            $customer = $seller->associatedAccount;
        } elseif ($seller == null && $customer?->seller_id != null) {
            $seller = $customer->associatedAccount;
        }

        $response['priority'] = $customer != null ? 'customer' : 'seller';

        // Log admin access
        DB::table('admin_login_logs')->insert([
            'phone' => $request->phone,
            'account_type' => $response['priority'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        // Create tokens for both accounts if they exist
        if ($customer != null) {
            $this->updateDeviceToken($customer, $request->device_token);
            $response['customer'] = [
                'token' => $customer->createToken($request->device_token)->plainTextToken,
                'verification_status' => $this->getVerificationStatus($customer)
            ];
        }

        if ($seller != null) {
            $this->updateDeviceToken($seller, $request->device_token);
            $response['seller'] = [
                'token' => $seller->createToken($request->device_token)->plainTextToken,
                'verification_status' => $this->getVerificationStatus($seller)
            ];
        }

        return $this->ApiResponseFormatted(200, $response, 'Admin login successful', $settings, $request);
    }

    private function updateDeviceToken($account, $token)
    {
        $deviceTokens = $account->tokens ?? [];
        if (!in_array($token, $deviceTokens)) {
            $deviceTokens[] = $token;
            $account->update([
                'tokens' => $deviceTokens
            ]);
        }
    }

    private function getVerificationStatus($account): array
    {
        return [
            'expires_at' => null,
            'cooldown' => false,
            'cooldown_for' => null,
            'phone_verified' => $account->phone_verified_at != null,
            'email_verified' => $account->email_verified_at != null,
            'phone' => $account->phone,
        ];
    }
}
