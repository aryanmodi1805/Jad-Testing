<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Response;
use App\Settings\AppSettings;
use Exception;
use Illuminate\Http\Request;
use Lang;

class RatingsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    protected function resolveRateableModel(string $type)
    {
        return match ($type) {
            'request' => \App\Models\Request::class,
            'response' => Response::class,
            'country' => Country::class,
            default => throw new \InvalidArgumentException('Invalid rateable type'),
        };
    }

    public function rate(Request $request, AppSettings $settings)
    {
        $validator = \Validator::make($request->all(), [
            'rateable_id' => 'required',
            'rateable_type' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->ApiResponseFormatted(422, $validator->errors(), Lang::get('api.validation_error'), $settings, $request);
        }


        $rateable = $this->resolveRateableModel($request->rateable_type)::find($request->rateable_id);
        $rater = auth()->user();

        if ($rateable == null || $rater == null) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.record_not_found'), $settings, $request);
        }

        try {
            $rateable->ratings()->updateOrCreate(
                [
                    'rateable_type' => $rateable::class,
                    'rater_type' => $rater::class,
                    'rater_id' => $rater->id,
                ],
                [
                    'rating' => $request->rating,
                    'review' => $request->review,
                    'language' => $request->header('Accept-Language')
                ]
            );
            return $this->ApiResponseFormatted(200, null, Lang::get('api.success'), $settings, $request);
        } catch (Exception $ex) {
            return $this->ApiResponseFormatted(500, null, $ex->getMessage(), $settings, $request);
        }


    }
}
