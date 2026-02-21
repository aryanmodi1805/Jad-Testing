<?php

namespace App\Http\Resources;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerLocationsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */


    public function toArray(Request $request): array
    {
        $services = Service::whereIn('id', $this->services->pluck('service_id')->toArray())->get();

        return [
            "id" => $this->id,
            "name" => $this->name,
            "location_name" => $this->location_name,
            "is_nationwide" => $this->is_nationwide,
            "latitude" => $this->latitude,
            "longitude" => $this->longitude,
            "location_range" => $this->location_range,
            "pivot" => $this->pivot,
            "services" => ServiceResource::collection($services),
        ];
    }
}
