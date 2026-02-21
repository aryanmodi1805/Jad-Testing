<?php

namespace App\Http\Controllers;

use App\Http\Resources\CountryResource;
use App\Http\Resources\QuestionResource;
use App\Http\Resources\RequestResource;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\SupportResource;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Scopes\TenantScope;
use App\Models\Service;
use App\Settings\AboutSettings;
use App\Settings\AppSettings;
use App\Settings\HeroesSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Lang;
use Wamania\Snowball\StemmerFactory;

class GuestController extends Controller
{
    public function mostRequested(Request $request, AppSettings $settings)
    {
        $countryId = $this->getCountryId($request);
        
        // Use cached data if available (15 minutes TTL)
        $mostRequestedServices = \Cache::remember("most_requested_{$countryId}", 60 * 15, function () use ($countryId) {
            return Service::query()->withoutGlobalScopes([TenantScope::class])
                ->select('id', 'name', 'image', 'category_id', 'country_id')
                ->where('country_id', $countryId)
                ->mostRequested(8)
                ->get();
        });
        
        return $this->ApiResponseFormatted(200, ServiceResource::collection($mostRequestedServices), Lang::get('api.success'), $settings, $request);
    }

    public function getHotServices(Request $request, AppSettings $settings)
    {
        $countryId = $this->getCountryId($request);
        
        // Use cached data if available (15 minutes TTL)
        $hotServices = \Cache::remember("hot_services_{$countryId}", 60 * 15, function () use ($countryId) {
            return Service::query()->withoutGlobalScopes([TenantScope::class])
                ->select('id', 'name', 'image', 'category_id', 'country_id')
                ->where('country_id', $countryId)
                ->hotNow(8)
                ->get();
        });
        
        return $this->ApiResponseFormatted(200, ServiceResource::collection($hotServices), Lang::get('api.success'), $settings, $request);
    }

    public function getLatestServices(Request $request, AppSettings $settings)
    {
        $countryId = $this->getCountryId($request);
        
        // Use cached data if available (15 minutes TTL)
        $latestServices = \Cache::remember("latest_services_{$countryId}", 60 * 15, function () use ($countryId) {
            return Service::query()->withoutGlobalScopes([TenantScope::class])
                ->select('id', 'name', 'image', 'category_id', 'country_id', 'created_at')
                ->where('country_id', $countryId)
                ->latest()
                ->limit(8)
                ->get();
        });
        
        return $this->ApiResponseFormatted(200, ServiceResource::collection($latestServices), Lang::get('api.success'), $settings, $request);
    }

    public function getHeroAttributes(Request $request, AppSettings $settings)
    {
        $herosSettings = app(HeroesSettings::class);

        $hero = $request->getUriForPath($herosSettings->getMainHero());

        $title = [
            'en' => $herosSettings->text_en,
            'ar' => $herosSettings->text_ar,
        ];

        return response()->json([
            'hero' => $hero,
            'title' => $title,
        ]);
    }

    public function countries(Request $request, AppSettings $settings)
    {
        // Cache only the columns needed by CountryResource
        $countries = \Cache::remember('api_countries', 60 * 60 * 24, function () {
            return Country::select('id', 'name', 'code', 'credit_price', 'vat_percentage')->get();
        });
        
        return $this->ApiResponseFormatted(200, CountryResource::collection($countries), Lang::get('api.success'), $settings, $request);
    }

    public function serviceQuestions(Request $request, AppSettings $settings)
    {
        if (!isset($request->id)) {
            return $this->ApiResponseFormatted(422, null, Lang::get('api.validation_error'), request: $request);
        }

        $country = Country::query()->find($this->getCountryId($request));

        if (!$country) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.country_not_found'), request: $request);
        }

        $service = Service::query()->withoutGlobalScopes([TenantScope::class])
            ->where('country_id', $country->id)
            ->where('id', $request->id)->first();

        if (!$service) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.service_not_found'), request: $request);
        }

        return $this->ApiResponseFormatted(200, [
            'lat' => $country->location['lat'] ?? 0,
            'lng' => $country->location['lng'] ?? 0,
            'location_name' => null,
            'questions' => QuestionResource::collection($service->questions),

        ], Lang::get('api.success'), $settings, $request);
    }

    public function serviceSearch(Request $request, AppSettings $settings)
    {
        $validateToken = Validator::make($request->all(), ['search' => 'required|string',]);

        if ($validateToken->fails()) {
            return $this->ApiResponseFormatted(422, null, Lang::get('api.validation_error'), $settings, request: $request);
        }

        $searchTerm = $request->get('search');

        $country = Country::query()->find($this->getCountryId($request));

        if (!$country) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.country_not_found'), $settings, request: $request);
        }

        // Select only columns needed by ServiceResource
        $services = Service::query()->withoutGlobalScopes([TenantScope::class])
            ->select('services.id', 'services.name', 'services.image', 'services.category_id')
            ->where('country_id', $country->id)
            ->join('categories as subcategories', 'services.category_id', '=', 'subcategories.id')
            ->join('categories as categories', 'subcategories.parent_id', '=', 'categories.id')
            ->where(fn($query) => $query->where(fn($query) => searchWithVariations($query, $searchTerm, 'services.name'))
                ->orWhere(fn($query) => searchWithVariations($query, $searchTerm, 'categories.name'))
                ->orWhere(fn($query) => searchWithVariations($query, $searchTerm, 'subcategories.name'))
                ->orWhereRaw('LOWER(keywords) like ?', ["%" . strtolower($searchTerm) . "%"])
            )->get();


        if ($services->isEmpty()) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.no_search_results'), $settings, request: $request);
        }

        return $this->ApiResponseFormatted(200, ServiceResource::collection($services), Lang::get('api.success'), $settings, $request);
    }

    public function searchSuggestions(Request $request, AppSettings $settings)
    {
        $validateToken = Validator::make($request->all(), ['query' => 'required|string|min:2']);

        if ($validateToken->fails()) {
            return $this->ApiResponseFormatted(422, null, Lang::get('api.validation_error'), $settings, request: $request);
        }

        $searchTerm = $request->query('query');
        $country = Country::query()->find($this->getCountryId($request));

        if (!$country) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.country_not_found'), $settings, request: $request);
        }

        $services = Service::query()->withoutGlobalScopes([TenantScope::class])
            ->select('services.id', 'services.name', 'services.image')
            ->where('country_id', $country->id)
            ->join('categories as subcategories', 'services.category_id', '=', 'subcategories.id')
            ->join('categories as categories', 'subcategories.parent_id', '=', 'categories.id')
            ->where(fn($query) => $query->where(fn($query) => searchWithVariations($query, $searchTerm, 'services.name'))
                ->orWhere(fn($query) => searchWithVariations($query, $searchTerm, 'categories.name'))
                ->orWhere(fn($query) => searchWithVariations($query, $searchTerm, 'subcategories.name'))
                ->orWhereRaw('LOWER(keywords) like ?', ["%".strtolower($searchTerm)."%"])
            )
            ->limit(5)
            ->get();

        $suggestions = $services->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'image' => $service->image,
            ];
        });

        return $this->ApiResponseFormatted(200, $suggestions, Lang::get('api.success'), $settings, request: $request);
    }

    public function getServices(Request $request, AppSettings $settings)
    {

        $country = Country::query()->find($this->getCountryId($request));

        if (!$country) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.country_not_found'), $settings, request: $request);
        }

        // Select only columns needed by ServiceResource
        $services = Service::query()->withoutGlobalScopes([TenantScope::class])
            ->select('id', 'name', 'image', 'category_id', 'country_id')
            ->where('country_id', $country->id)
            ->paginate(20);

        return $this->ApiResponseFormatted(200, ServiceResource::collection($services), Lang::get('api.success'), $settings, $request);


    }


    public function getSupport(Request $request, AppSettings $settings)
    {
        $aboutSttings = app(AboutSettings::class);

        return $this->ApiResponseFormatted(200, SupportResource::make($aboutSttings), Lang::get('api.success'), $settings, $request);
    }

    public function getCategories(Request $request, AppSettings $settings)
    {
        // Use cached data if available (15 minutes TTL)
        $categories = \Cache::remember('home_categories', 60 * 15, function () {
            return Category::active()
                ->whereNull('parent_id')
                ->withCount('services')
                ->orderBy('services_count', 'desc')
                ->get();
        });

        return $this->ApiResponseFormatted(200, CategoryResource::collection($categories), Lang::get('api.success'), $settings, $request);
    }

    public function getCategoryServices(Request $request, AppSettings $settings)
    {
        if (!isset($request->id)) {
            return $this->ApiResponseFormatted(422, null, Lang::get('api.validation_error'), request: $request);
        }

        $country = Country::query()->find($this->getCountryId($request));

        if (!$country) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.country_not_found'), $settings, request: $request);
        }

        $category = Category::query()->find($request->id);

        if (!$category) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.category_not_found'), $settings, request: $request);
        }

        // If it's a parent category, get services from all subcategories
        if ($category->parent_id === null) {
            // Only get subcategory IDs, not full services (we'll query services separately)
            $subcategoryIds = Category::query()->where('parent_id', $category->id)
                ->has('services')
                ->pluck('id');
            
            // Select only columns needed by ServiceResource
            $services = Service::query()
                ->select('id', 'name', 'image', 'category_id')
                ->whereIn('category_id', $subcategoryIds)
                ->mostRequested(8)
                ->paginate(20);
        } else {
            // If it's a subcategory, get services directly
            $services = Service::query()
                ->select('id', 'name', 'image', 'category_id')
                ->where('category_id', $category->id)
                ->paginate(20);
        }

        return $this->ApiResponseFormatted(200, ServiceResource::collection($services), Lang::get('api.success'), $settings, $request);
}

    /**
     * Combined endpoint for homepage - returns all data in one call
     * Reduces 4 API calls to 1 for faster homepage loading
     */
    public function getHomeInit(Request $request, AppSettings $settings)
    {
        $countryId = $this->getCountryId($request);
        $herosSettings = app(HeroesSettings::class);
        
        // If refresh is requested, clear the cache keys
        if ($request->has('refresh') && $request->refresh == 'true') {
            \Cache::forget('home_hero');
            \Cache::forget("latest_services_{$countryId}");
            \Cache::forget("hot_services_{$countryId}");
            \Cache::forget('home_categories');
        }
        
        // Get hero data (cached)
        $heroData = \Cache::remember('home_hero', 60 * 15, function () use ($herosSettings) {
            return [
                'hero_path' => $herosSettings->getMainHero(),
                'title' => [
                    'en' => $herosSettings->text_en,
                    'ar' => $herosSettings->text_ar,
                ],
            ];
        });
        
        // Get latest services (cached)
        $latestServices = \Cache::remember("latest_services_{$countryId}", 60 * 15, function () use ($countryId) {
            return Service::query()->withoutGlobalScopes([TenantScope::class])
                ->select('id', 'name', 'image', 'category_id', 'country_id', 'created_at')
                ->where('country_id', $countryId)
                ->latest()
                ->limit(8)
                ->get();
        });
        
        // Get hot services (cached)
        $hotServices = \Cache::remember("hot_services_{$countryId}", 60 * 15, function () use ($countryId) {
            return Service::query()->withoutGlobalScopes([TenantScope::class])
                ->select('id', 'name', 'image', 'category_id', 'country_id')
                ->where('country_id', $countryId)
                ->hotNow(8)
                ->get();
        });
        
        // Get categories (cached)
        $categories = \Cache::remember('home_categories', 60 * 15, function () {
            return Category::active()
                ->whereNull('parent_id')
                ->withCount('services')
                ->orderBy('services_count', 'desc')
                ->get();
        });
        
        return $this->ApiResponseFormatted(200, [
            'hero' => $request->getUriForPath($heroData['hero_path']),
            'title' => $heroData['title'],
            'latest_services' => ServiceResource::collection($latestServices),
            'hot_services' => ServiceResource::collection($hotServices),
            'categories' => CategoryResource::collection($categories),
        ], Lang::get('api.success'), $settings, $request);
    }

}
