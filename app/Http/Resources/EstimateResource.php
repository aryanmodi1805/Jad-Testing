<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EstimateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'amount' => $this->amount,
            'details' => $this->details,
            'amount_per_base' => $this->amountPerBase,
            'estimate_base_name' => $this->estimateBase?->name,
        ];
    }
}
