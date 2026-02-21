<?php

namespace App\Http\Resources;

use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var $this \App\Models\Request */
        return [
            "id" => $this->id,
            "customer" => CustomerResource::make($this->customer),
            "service" => ServiceResource::make($this->service),
            "status" => $this->status,
            "responses_count" => $this->responses_count ?? 0,
            "created_since" => $this->created_at->diffForHumans(),
            "is_invited" => $this->is_invited ?? null,
            "request_total_cost" => $this->request_total_cost ?? null,
            "latitude" => $this->latitude ?? null,
            "longitude" => $this->longitude ?? null,
        ];
    }
}
