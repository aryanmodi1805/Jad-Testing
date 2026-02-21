<?php

namespace App\Http\Controllers;

use App\Http\Resources\SellerLocationsResource;
use App\Http\Resources\SellerServicesAndLocationsResource;
use App\Models\SellerServiceLocation;
use App\Settings\AppSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Lang;

class SellerServicesController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }


    public function getServices(Request $request, AppSettings $settings)
    {
//       $sellerServices["services"] =  SellerServicesAndLocationsResource::collection($request->user()->sellerServices()->with(['service',"locations"])->whereHas("service")->get());
//       $sellerServices["locations"] = $request->user()->sellerLocations()->get();
//       $sellerServices = $request->user()->serviceLocation()->get();
//       $sellerServices = $request->user()->sellerServices()->whereHas("service")->get();
//       $sellerServices = $request->user()->services()->get();
        $sellerServices = SellerServicesAndLocationsResource::collection($request->user()->sellerServices()->with(['service', "locations"])->whereHas("service")->get());;
        return $this->ApiResponseFormatted(200, $sellerServices, Lang::get('api.success'), $settings, $request);
//        return $this->ApiResponseFormatted(200, SellerServicesAndLocationsResource::collection($sellerServices), Lang::get('api.success'), $settings, $request);

    }


    public function removeService(Request $request, AppSettings $settings)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->ApiResponseFormatted(400, [], $validator->errors()->first(), $settings, $request);
        }

        $seller = $request->user();
        $sellerService = $seller->sellerServices()->where('service_id', $request->input('id'))->first();

        if (!$sellerService) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.service_not_found'), $settings, $request);
        }

        $sellerService->delete();

        return $this->ApiResponseFormatted(200, null, Lang::get('api.success'), $settings, $request);
    }


    public function getLocations(Request $request, AppSettings $settings)
    {

        $sellerLocations = $request->user()->sellerLocations()->paginate(10);


        return $this->ApiResponseFormatted(200, SellerLocationsResource::collection($sellerLocations->items()), Lang::get('api.success'), $settings, $request);

    }

    public function addLocation(Request $request, AppSettings $settings)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'integer',
            'name' => 'required',
            'location_name' => 'string',
            'latitude' => 'numeric',
            'longitude' => 'numeric',
            'location_range' => 'integer',
            'services_ids' => 'array',
            'is_nationwide' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->ApiResponseFormatted(400, [], $validator->errors()->first(), $settings, $request);
        }

        $seller = $request->user();
        $locationData = $request->only([
            'name',
            'location_name',
            'latitude',
            'longitude',
            'location_range',
            'is_nationwide'
        ]);


        $location = null;

        if ($request->is_nationwide) {
            $location = $seller->sellerLocations()->where('is_nationwide', 1)->first();

            // Create a new nationwide location if none exists
            if (!$location) {
                $location = $seller->sellerLocations()->create($locationData);
            } else {
                $location->update($locationData);
            }

        } elseif ($request->id) {
            $location = $seller->sellerLocations()->find($request->id);

            if (!$location) {
                return $this->ApiResponseFormatted(404, null, Lang::get('api.location_not_found'), $settings, $request);
            }

            $location->update($locationData);
        } else {
            $location = $seller->sellerLocations()->create($locationData);
        }

        if ($request->services_ids) {
            $services = $seller->sellerServices()->whereIn('service_id', $request->services_ids)->get();

            foreach ($services as $service) {
                SellerServiceLocation::firstOrCreate(
                    [
                        'seller_service_id' => $service->id,
                        'seller_location_id' => $location->id,
                    ],
                    [
                        'is_nationwide' => $location->is_nationwide,
                        'location_range' => $location->location_range
                    ]
                );
            }
        }


        return $this->ApiResponseFormatted(200, null, Lang::get('api.success'), $settings, $request);
    }

    public function removeLocation(Request $request, AppSettings $settings)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->ApiResponseFormatted(400, [], $validator->errors()->first(), $settings, $request);
        }

        $seller = $request->user();
        $location = $seller->sellerLocations()->find($request->id);

        if (!$location) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.location_not_found'), $settings, $request);
        }

        $location->delete();

        return $this->ApiResponseFormatted(200, null, Lang::get('api.success'), $settings, $request);
    }


}
