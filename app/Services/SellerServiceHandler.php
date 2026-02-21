<?php
namespace App\Services;

use App\Enums\RequestStatus;
use App\Models\SellerLocation;
use App\Models\SellerService;
use App\Models\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SellerServiceHandler
{
    public function filterRequests(array $selectedServices, array $selectedLocations): Collection
    {
        $requestsQuery = Request::query()->currentCountry()->where('status', '!=', RequestStatus::Pending->value);

        if (!empty($selectedServices)) {
            $requestsQuery->whereHas('service', function ($query) use ($selectedServices) {
                $query->whereIn('id', $selectedServices);
            });
        } else {
            return collect(); // إذا لم يتم اختيار خدمات، فلا يتم تحميل أي طلبات
        }

        if (!empty($selectedLocations)) {
            $requestsQuery->where(function ($query) use ($selectedLocations, $selectedServices) {
                $this->filterLocationsWithinRange($query, $selectedLocations);
                $this->filterNationwideLocations($query, $selectedLocations, $selectedServices);
            });
        } else {
            return collect(); // إذا لم تكن هناك مواقع محددة، لا تعرض أية طلبات إلا إذا كانت الخدمات مرتبطة بـ nationwide
        }

        return $requestsQuery->get();
    }

    private function filterLocationsWithinRange($query, $selectedLocations)
    {
        $query->whereExists(function ($query) use ($selectedLocations) {
            $query->select(DB::raw(1))
                ->from('seller_service_locations')
                ->join('seller_locations', 'seller_service_locations.seller_location_id', '=', 'seller_locations.id')
                ->join('seller_services', 'seller_service_locations.seller_service_id', '=', 'seller_services.id')
                ->whereColumn('seller_services.service_id', 'requests.service_id')
                ->whereRaw("ST_Distance_Sphere(point(seller_locations.longitude, seller_locations.latitude), point(requests.longitude, requests.latitude)) <= seller_locations.`location_range` * 1000")
                ->whereIn('seller_locations.id', $selectedLocations)
                ->where('seller_locations.is_nationwide', false);
        });
    }

    private function filterNationwideLocations($query, $selectedLocations, $selectedServices)
    {
        if (SellerLocation::whereIn('id', $selectedLocations)->where('is_nationwide', true)->exists()) {
            $query->orWhereExists(function ($query) use ($selectedLocations, $selectedServices) {
                $query->select(DB::raw(1))
                    ->from('seller_service_locations')
                    ->join('seller_locations', 'seller_service_locations.seller_location_id', '=', 'seller_locations.id')
                    ->join('seller_services', 'seller_service_locations.seller_service_id', '=', 'seller_services.id')
                    ->whereColumn('seller_services.service_id', 'requests.service_id')
                    ->where('seller_locations.is_nationwide', true)
                    ->whereIn('seller_services.service_id', $selectedServices);
            });
        }
    }
}
