<?php

namespace App\Http\Controllers;

use App\Concerns\ApiResponseFormat;
use App\Http\Resources\CountryResource;
use App\Http\Resources\DeliveryAreaResource;
use App\Models\Country;
use App\Models\Customer;
use App\Models\DeliveryArea;
use App\Models\Factory;
use App\Settings\AppSettings;
use App\Settings\GeneralSettings;
use App\Utils\LocationHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{

    use ApiResponseFormat;

    public function customerToken(Request $request)
    {
        if (auth('customer')->check()) {
            return $this->processToken($request, auth('customer')->user());
        } else {
            return $this->ApiResponseFormatted(200);
        }
    }

    public function sellerToken(Request $request)
    {
        if (auth('seller')->check()) {
            return $this->processToken($request, auth('seller')->user());
        } else {
            return $this->ApiResponseFormatted(200);
        }
    }

    public function adminToken(Request $request)
    {
        if (auth('admin')->check()) {
            return $this->processToken($request, auth('admin')->user());
        } else {
            return $this->ApiResponseFormatted(200);
        }
    }

   public function processToken(Request $request , $user)
    {
        $validateToken = Validator::make($request->all(), ['token' => 'required|string',]);

        if ($validateToken->fails()) {
            return $this->ApiResponseFormatted(422,null, 'VALIDATION_ERROR', request: $request);
        } else {
            $tokens = $user->tokens ?? [];
            if (!in_array($request->token, $tokens)) {
                $tokens[] = $request->token;
                $user->update(['tokens' => $tokens]);
                return $this->ApiResponseFormatted(200, 'Success', 'Success', request:$request);

            } else {
                return $this->ApiResponseFormatted(200, 'None', 'None', request: $request);
            }
        }

    }


}
