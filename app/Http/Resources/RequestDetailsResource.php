<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class RequestDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $price = $this->getFinalPrice();

        return [
            'id' => $this->id,
            'customer' => CustomerResource::make($this->customer),
            'service' => ServiceResource::make($this->service),
            'status' => $this->status,
            'location_name' => $this->location_name,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'location_type' => $this->location_type,
            'is_request_purchased' => $this->is_request_purchased,
            'responses_count' => $this->responses_count,
            'is_invited' => $this->is_invited,
            'request_total_cost' => $this->request_total_cost,
            'price'=> $price,
            'is_enough_funds' => (function() use ($request, $price) {
                $user = $request->user();
                if ($user instanceof \App\Models\Seller) {
                    $settings = app(\App\Settings\AppSettings::class);
                    $minBalance = $settings->minimum_seller_wallet_balance;
                    // Seller needs to maintain minimum balance to connect
                    return $user->balance()->value->greaterThanOrEqual($minBalance);
                }
                return $user->isEnoughFunds($price);
            })(),
            "answers" =>$this->formattedAnswers(),

        ];
    }
}
