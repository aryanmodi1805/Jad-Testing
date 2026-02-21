<?php

namespace App\Services;

use App\Models\BlockReport;
use App\Models\Request;
use App\Models\SellerLocation;
use App\Models\SellerService;
use App\Models\Seller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpParser\Builder;

class RequestServiceHandler
{
    public function filterSellersForRequest($requestId, $sortByRating = null, $sortByDistance = null): Collection
    {
        $request = Request::find($requestId);

        if (!$request) {
            return collect(); // إذا لم يتم العثور على الطلب
        }

        $serviceId = $request->service_id;
        $requestLongitude = $request->longitude;
        $requestLatitude = $request->latitude;

        // احصل على جميع seller_services التي لديها نفس service_id
        $sellerServices = SellerService::with([
            'seller',
            'locations'
        ])->notBlocked($request->customer_id)->where('service_id', $serviceId)->get();

        // تحقق من أن مواقع البائع تغطي موقع الطلب
        $matchingSellers = collect();
        foreach ($sellerServices as $sellerService) {
            $seller = $sellerService->seller;

            foreach ($sellerService->locations as $location) {
                if ($location->is_nationwide || $this->isLocationInRange($location, $requestLongitude, $requestLatitude)) {
                    $distance = $this->calculateDistance($location, $requestLongitude, $requestLatitude);
                    $seller->distance = $distance;
                    $seller->average_rating = $seller->averageRating();
                    $matchingSellers->push($seller);
                    break;
                }
            }
        }

        if ($sortByDistance) {
            $matchingSellers = $matchingSellers->sortBy([
                ['distance', $sortByDistance]
            ]);
        }elseif ($sortByRating) {
            $matchingSellers = $matchingSellers->sortBy([
                ['average_rating', $sortByRating]
            ]);
        }

        return $matchingSellers;
    }

    private function isLocationInRange($location, $longitude, $latitude)
    {
        $distance = $this->calculateDistance($location, $longitude, $latitude);

        return $distance <= $location->range * 1000;
    }

    private function calculateDistance($location, $longitude, $latitude)
    {
        $distance = DB::selectOne("SELECT ST_Distance_Sphere(point(?, ?), point(?, ?)) as distance", [
            $location->longitude,
            $location->latitude,
            $longitude,
            $latitude
        ]);

        return $distance->distance;
    }
}
