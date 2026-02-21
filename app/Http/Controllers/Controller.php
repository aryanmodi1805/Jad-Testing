<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Seller;
use App\Settings\AppSettings;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;


    protected function ApiResponseFormatted($code = 401, $data = NULL, $message = "Unauthenticated",?AppSettings $settings = null, ?Request $request = null): JsonResponse
    {
        $headers =[];

        if($settings != null){
            $headers["ios_app_active"] = $settings->ios_app_active;
            $headers["android_app_active"] = $settings->android_app_active;
            $headers["ios_min_app_version"] = $settings->ios_min_app_version;
            $headers["android_min_app_version"] = $settings->android_min_app_version;
        }

        if($request != null && $request->user() != null){
            /* @var $user Customer | Seller*/
            $user = $request->user();
            $headers["phone_verified_at"] = $user->phone_verified_at;
            $headers["email_verified_at"] = $user->email_verified_at;
        }

        if($data == null){
            $data = ['message' => $message, 'data' => null];
        }

        // Sanitize the message to remove newlines/carriage returns - HTTP reason phrases cannot contain these
        $sanitizedMessage = str_replace(["\r", "\n"], ' ', $message);
        return response()->json($data)->setStatusCode($code, $sanitizedMessage)->withHeaders($headers);
    }

    protected function getCountryId(Request $request): int
    {
        return (int)$request->header('x-country-id-header');
    }

}
