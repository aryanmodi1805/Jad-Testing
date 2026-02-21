<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResponseResource extends JsonResource
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
            "service" => ServiceResource::make($this->service),
            "status"=> $this->status,
            "estimate"=> EstimateResource::make($this->estimate),
            "seller"=> SellerResource::make($this->seller),
            "customer"=> CustomerResource::make($this->customer),
            "created_since"=> $this->created_at->diffForHumans(),
            'request' => RequestResource::make($this->request),
            'cash_payment_available' => $this->canPayCash(),
        ];
    }
}
