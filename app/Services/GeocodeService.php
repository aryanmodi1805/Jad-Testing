<?php

namespace App\Services;

use App\Models\Request;
use App\Models\SellerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class GeocodeService
{
    public function getCountryCode($lat, $lng)
    {
        $apiKey = config('filament-google-maps.key');
        $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$lat},{$lng}&result_type=country&key={$apiKey}";

        $response = Http::get($url);
        $data = $response->json();

        if (!empty($data['results'])) {
            return $data['results'][0]['address_components'][0]['short_name'] ?? null;
        }

        return null;
    }
}
