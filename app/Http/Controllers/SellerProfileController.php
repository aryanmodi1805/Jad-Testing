<?php

namespace App\Http\Controllers;

use App\Http\Resources\MediaResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\SellerMyServicesResource;
use App\Http\Resources\SellerProfileResource;
use App\Http\Resources\SellerQASResource;
use App\Http\Resources\SellerReviewsResource;
use App\Http\Resources\SellerServicesResource;
use App\Http\Resources\ServiceResource;
use App\Models\Country;
use App\Models\Scopes\TenantScope;
use App\Models\Seller;
use App\Models\Service;
use App\Settings\AppSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Lang;

class SellerProfileController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }


    public function getSellerProfile(Request $request, AppSettings $settings, $id)
    {
        $seller = Seller::where('id', $id)->first();
        try {
            if (!$seller) {
                return response()->json(['error' => Lang::get('api.seller_not_found')], 404);
            }

            return $this->ApiResponseFormatted(200, SellerProfileResource::make($seller), Lang::get('api.success'), $settings, $request);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getProfile(Request $request, AppSettings $settings)
    {
        $seller = Seller::where('id', $request->user()->id)->first();
        try {
            if (!$seller) {
                return response()->json(['error' => Lang::get('api.seller_not_found')], 404);
            }

            return $this->ApiResponseFormatted(200, SellerProfileResource::make($seller), Lang::get('api.success'), $settings, $request);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getMyReviews(Request $request, AppSettings $settings)
    {
        $seller = Seller::where('id', $request->user()->id)->first();
        try {
            if (!$seller) {
                return response()->json(['error' => Lang::get('api.seller_not_found')], 404);
            }


            return $this->ApiResponseFormatted(200, SellerReviewsResource::make($seller), Lang::get('api.success'), $settings, $request);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getMyServices(Request $request, AppSettings $settings)
    {
        $seller = Seller::where('id', $request->user()->id)->first();
        try {
            if (!$seller) {
                return response()->json(['error' => Lang::get('api.seller_not_found')], 404);
            }


            return $this->ApiResponseFormatted(200, SellerMyServicesResource::collection($seller->sellerProfileServices), Lang::get('api.success'), $settings, $request);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function saveMyServices(Request $request, AppSettings $settings)
    {
        $validate = Validator::make($request->all(), [
            'service_title.ar' => 'required|string',
            'service_title.en' => 'required|string',
            'service_description.ar' => 'required|string|sometimes',
            'service_description.en' => 'required|string|sometimes',
            'id' => 'int',
        ]);
        $validate->setAttributeNames([
            'service_title.ar' => Lang::get('seller.seller_profile_services.service_title_ar'),
            'service_title.en' => Lang::get('seller.seller_profile_services.service_description'),
            'service_description.ar' => Lang::get('seller.seller_profile_services.service_description_ar'),
            'service_description.en' => Lang::get('seller.seller_profile_services.service_description'),
        ]);


        if ($validate->fails()) {
            $error = implode("|", $validate->errors()->all());

            return $this->ApiResponseFormatted(422, null, empty($error) ? Lang::get('api.validation_error') : $error, $settings, $request);
        }

        $seller = Seller::where('id', $request->user()->id)->first();


        try {
            if (!$seller) {
                return response()->json(['error' => Lang::get('api.seller_not_found')], 404);
            }
            if ($request->id == 0)
                $request->id = null;

            $seller->sellerProfileServices()->updateOrCreate(['id' => $request->id], [
                'service_title' => $request->service_title,
                'service_description' => $request->service_description,
            ]);
            return $this->ApiResponseFormatted(200, null, Lang::get('api.success'), $settings, $request);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteMyServices(Request $request, AppSettings $settings)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|int',

        ]);


        if ($validate->fails()) {

            return $this->ApiResponseFormatted(422, null, Lang::get('api.validation_error'), $settings, $request);
        }

        $seller = Seller::where('id', $request->user()->id)->first();


        try {
            if (!$seller) {
                return response()->json(['error' => Lang::get('api.seller_not_found')], 404);
            }
            $seller->sellerProfileServices()->where('id', $request->id)->delete();
            return $this->ApiResponseFormatted(200, null, Lang::get('api.success'), $settings, $request);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getGallery(Request $request, AppSettings $settings)
    {
        $seller = Seller::where('id', $request->user()->id)->first();
        try {
            if (!$seller) {
                return $this->ApiResponseFormatted(404, null, Lang::get('api.seller_not_found'), $settings, $request);
            }

            $gallery = $seller->getMedia('images');

            return $this->ApiResponseFormatted(200, MediaResource::collection($gallery), Lang::get('api.success'), $settings, $request);

        } catch (\Exception $e) {
            return $this->ApiResponseFormatted(500, null, $e->getMessage(), $settings, $request);
        }
    }

    public function saveGallery(Request $request, AppSettings $settings)
    {
        $validate = Validator::make($request->all(), [
            'ids' => 'array',
        ]);

        if ($validate->fails()) {
            return $this->ApiResponseFormatted(422, null, Lang::get('api.validation_error'), $settings, $request);
        }

        $seller = Seller::where('id', $request->user()->id)->first();

        if (!$seller) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.seller_not_found'), $settings, $request);
        }

        if (isset($request->ids)) {
            $seller->media()->whereIn('id', $request->ids)->delete();
        }

        if (isset($request->allFiles()['files'])) {
            $requestFiles = $request->allFiles()['files'];

            foreach ($requestFiles as $file) {
                $seller->addMedia($file)->toMediaCollection('images')->checkWidthHeight();
            }
        }

        return $this->ApiResponseFormatted(200, null, Lang::get('api.success'), $settings, $request);

    }

    public function getSellerServices(Request $request, AppSettings $settings)
    {
        $seller = Seller::where('id', $request->user()->id)->first();
        try {
            if (!$seller) {
                return $this->ApiResponseFormatted(404, null, Lang::get('api.seller_not_found'), $settings, $request);
            }

            $data = $request->user()->sellerServices()->with(['service'])->withCount("locations")->whereHas("service")->paginate(20);
            $services = collect($data->items())->map(function ($item) {
                $item->service["locations_count"] = $item->locations_count;
                return $item->service;
            });





            return $this->ApiResponseFormatted(200, ServiceResource::collection($services), Lang::get('api.success'), $settings, $request);

        } catch (\Exception $e) {
            return $this->ApiResponseFormatted(500, null, $e->getMessage(), $settings, $request);
        }
    }

    public function getServices(Request $request, AppSettings $settings)
    {

        $validateToken = Validator::make($request->all(), ['search' => 'string|nullable',]);

        if ($validateToken->fails()) {
            return $this->ApiResponseFormatted(422, null, Lang::get('api.validation_error'), $settings, request: $request);
        }

        $searchTerm = $request->search;

        $country = Country::query()->find($this->getCountryId($request));

        if (!$country) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.country_not_found'), $settings, request: $request);
        }

        $services = Service::query()->withoutGlobalScopes([TenantScope::class])
            ->select('services.*')
            ->notAssignedToSeller(auth()->id())
            ->where('country_id', $country->id)
            ->when($searchTerm, function ($query, $searchTerm) {
                $query->where(function ($query) use ($searchTerm) {
                    searchWithVariations($query, $searchTerm, 'services.name');
                });

            })
            ->paginate(10);


        return $this->ApiResponseFormatted(200, ServiceResource::collection($services->items()), Lang::get('api.success'), $settings, $request);


    }

    public function addServicesToSeller(Request $request, AppSettings $settings)
    {
        $validate = Validator::make($request->all(), [
            'ids' => 'required|array',
        ]);

        if ($validate->fails()) {
            return $this->ApiResponseFormatted(422, null, Lang::get('api.validation_error'), $settings, request: $request);
        }

        $seller = Seller::where('id', $request->user()->id)->first();

        if (!$seller) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.seller_not_found'), $settings, $request);
        }

        $seller->services()->syncWithoutDetaching($request->ids);

        return $this->ApiResponseFormatted(200, null, Lang::get('api.success'), $settings, $request);

    }

    public function getSellerProjects(Request $request, AppSettings $settings)
    {
        $seller = Seller::where('id', $request->user()->id)->first();
        try {
            if (!$seller) {
                return $this->ApiResponseFormatted(404, null, Lang::get('api.seller_not_found'), $settings, $request);
            }

            $projects = $seller->projects()->paginate(10);

            return $this->ApiResponseFormatted(200, ProjectResource::collection($projects->items()), Lang::get('api.success'), $settings, $request);





        } catch (\Exception $e) {
            return $this->ApiResponseFormatted(500, null, $e->getMessage(), $settings, $request);
        }
    }

    public function addSellerProject(Request $request, AppSettings $settings)
    {
        $validate = Validator::make($request->all(), [
            'title_ar' => 'required|string',
            'title_en' => 'required|string',
            'main_media' => 'required|array',
            'more_media' => 'array',
        ]);

        if ($validate->fails()) {
            return $this->ApiResponseFormatted(422, null, Lang::get('api.validation_error'), $settings, request: $request);
        }

        $seller = Seller::where('id', $request->user()->id)->first();

        if (!$seller) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.seller_not_found'), $settings, $request);
        }

        $title = [
            'en' => $request->title_en,
            'ar' => $request->title_ar,
        ];

        $project = $seller->projects()->create([
            'title' => $title,
        ]);

        if (isset($request->allFiles()['main_media'])) {
            $requestMainMedia = $request->allFiles()['main_media'];
           if($requestMainMedia){
               $project->addMedia($requestMainMedia[0])->toMediaCollection('projects.main')->checkWidthHeight();
           }
        }

        if (isset($request->allFiles()['more_media'])) {
            $requestFiles = $request->allFiles()['more_media'];

            foreach ($requestFiles as $file) {
                $project->addMedia($file)->toMediaCollection('projects.more')->checkWidthHeight();
            }

        }


        return $this->ApiResponseFormatted(200, null, Lang::get('api.success'), $settings, $request);

    }

    public function deleteSellerProject(Request $request, AppSettings $settings)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|int',
        ]);

        if ($validate->fails()) {
            return $this->ApiResponseFormatted(422, null, Lang::get('api.validation_error'), $settings, request: $request);
        }

        $seller = Seller::where('id', $request->user()->id)->first();

        if (!$seller) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.seller_not_found'), $settings, $request);
        }

        $project = $seller->projects()->find($request->id);

        if (!$project) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.record_not_found'), $settings, $request);
        }

        $project->delete();

        return $this->ApiResponseFormatted(200, null, Lang::get('api.success'), $settings, $request);

    }


    public function getSellerQAs(Request $request, AppSettings $settings)
    {
        $seller =  $request->user();
        try {

            $qas = $seller->qas()->paginate(10);

            return $this->ApiResponseFormatted(200, SellerQASResource::collection($qas->items()), Lang::get('api.success'), $settings, $request);

        } catch (\Exception $e) {
            return $this->ApiResponseFormatted(500, null, $e->getMessage(), $settings, $request);
        }
    }

    public function saveSellerQAs(Request $request, AppSettings $settings)
    {
        $validate = Validator::make($request->all(), [
            'question_ar' => 'required|string',
            'question_en' => 'required|string',
            'answer_ar' => 'required|string',
            'answer_en' => 'required|string',
            'id' => 'int',
        ]);

        if ($validate->fails()) {
            return $this->ApiResponseFormatted(422, null, Lang::get('api.validation_error'), $settings, request: $request);
        }
        try {

            $seller =  $request->user();

            if ($request->id == 0)
                $request->id = null;

            $seller->qas()->updateOrCreate(
                ['id' => $request->id],
                [
                    'question' => [
                        'ar' => $request->question_ar,
                        'en' => $request->question_en,
                    ],
                    'answer' => [
                        'ar' => $request->answer_ar,
                        'en' => $request->answer_en,
                    ],
                ]
            );



            return $this->ApiResponseFormatted(200, null, Lang::get('api.success'), $settings, $request);
        }catch (\Exception $e) {
            return $this->ApiResponseFormatted(500, null, $e->getMessage(), $settings, $request);
        }

    }





}
