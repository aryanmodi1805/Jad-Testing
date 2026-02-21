<?php

namespace App\Http\Resources;

use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerProfileResource extends JsonResource
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
            "seller" => SellerResource::make($this),
            "cover" => appUrl($this->cover_image),
            "social_media" => $this->socialMedia,
            "gallery" => MediaResource::collection($this->getMedia('images')),
            "bio"=> $this->getBio(),
            "years_in_business"=> $this->years_in_business ?? 0,
            "hired_projects_count"=> $this->hiredProjectsCount() ?? 0,
            "seller_profile_services"=> SellerServicesResource::collection($this->sellerProfileServices),
            "projects"=> ProjectResource::collection($this->projects),
            "qas"=> SellerQASResource::collection($this->qas),
            "profile_completion_rate"=>$this->profileCompletionRate()










        ];
    }
}
