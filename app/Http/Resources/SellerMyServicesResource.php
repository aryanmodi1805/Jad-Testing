<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerMyServicesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "service_title"=> [
                "en"=> $this->getTranslation("service_title", "en"),
                "ar"=> $this->getTranslation("service_title", "ar"),
            ],
            "service_description"=> [
                "en"=> $this->getTranslation("service_description", "en"),
                "ar"=> $this->getTranslation("service_description", "ar"),
            ],
        ];
    }
}
