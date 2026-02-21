<?php

namespace App\Concerns;


use App\Models\Customer;
use App\Settings\AppSettings;
use App\Settings\GeneralSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait ApiResponseFormat
{
    protected function ApiResponseFormatted($code = 401, $data = NULL, $message = "Unauthenticated",AppSettings $settings = null, Request $request = null): JsonResponse
    {
        $headers =[];
        if($settings != null){
            $headers["ios_app_active"] = $settings->ios_app_active;
            $headers["android_app_active"] = $settings->android_app_active;
            $headers["ios_min_app_version"] = $settings->ios_min_app_version;
            $headers["android_min_app_version"] = $settings->android_min_app_version;
        }

        // Sanitize the message to remove newlines/carriage returns - HTTP reason phrases cannot contain these
        $sanitizedMessage = str_replace(["\r", "\n"], ' ', $message);
        return response()->json($data)->setStatusCode($code, $sanitizedMessage)->withHeaders($headers);
    }
}
