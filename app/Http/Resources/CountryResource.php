<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
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
            "name" => $this->name,
            "code"=> $this->code,
            "credit_price" => $this->credit_price ?? 1,
            "vat_percentage" => $this->vat_percentage ?? 0,
        ];
    }
}
