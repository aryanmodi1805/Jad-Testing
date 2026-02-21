<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProfileResource;
use App\Models\Customer;
use App\Models\Seller;
use App\Settings\AppSettings;
use Illuminate\Http\Request;
use Lang;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function getCustomerProfile(Request $request, AppSettings $settings)
    {
        $customer = Customer::where('id', $request->user()->id)->first();


        try {
            if (!$customer) {
                return response()->json(['error' => Lang::get('api.customer_not_found')], 404);
            }
            return $this->ApiResponseFormatted(200, ProfileResource::make($customer), Lang::get('api.success'), $settings, $request);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


}
