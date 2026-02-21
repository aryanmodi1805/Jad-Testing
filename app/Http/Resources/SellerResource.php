<?php

namespace App\Http\Resources;

use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var $this Seller*/
        return [
            "id" => $this->id,
            "name" => filled($this->company_name)? $this->company_name :$this->name,
            "phone"=> $this->phone,
            "email"=> $this->email,
            "website"=> $this->website,
            "avatar"=> appUrl($this->avatar_url),
            // Use whenLoaded to prevent N+1 queries - only include if eager loaded
            "services"=> ServiceResource::collection($this->whenLoaded('services', $this->services, collect())),
            "ratings"=> RatingResource::collection($this->whenLoaded('ratings', $this->ratings, collect())),
            "created_at"=> $this->created_at,
            "ratings_avg"=> $this->rate,
            "ratings_count"=> $this->rate_count,
            "distance"=> $this->distance != null ? round($this->distance, 2) : null,

        ];
    }
}
