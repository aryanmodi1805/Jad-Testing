<?php

namespace App\Http\Resources;

use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerReviewsResource extends JsonResource
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
            "rate" =>doubleval( $this->rate),
            "rate_count" => $this->rate_count,
            "reviews" => $this->ratings()->where("rater_type","App\Models\Customer")->where("approved",true)->with(['rater'=> function ($query) {
                $query->select('name', 'avatar_url','id');
            }])->orderBy('id','desc')->get()->map(function($item){
                return [
                    'id'=>$item->id,
                    'rater'=>ProfileResource::make($item->rater),
                    'rating'=>doubleval($item->rating),
                    'review'=>$item->review,
                    'created_at'=>$item->created_at->diffForHumans(),

                ];
            }),
        ];
    }
}
