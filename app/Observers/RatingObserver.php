<?php

namespace App\Observers;

use App\Models\Rating;
use App\Models\Request;
use App\Models\Response;
use App\Models\Seller;
use App\Notifications\CreatedSellerRateNotification;
use App\Notifications\UpdatedSellerRateNotification;

class RatingObserver
{
    /**
     * Handle the Rating "created" event.
     */
    public function created(Rating $rating): void
    {
        if($rating->rateable_type == Response::class){
            $response = $rating->rateable;
            $request = $response->request;

            $seller = $response->seller;
            $seller->updateRating();
            $seller->notify(new CreatedSellerRateNotification($request, $response, $request->country));

        }elseif ($rating->rateable_type == Request::class){
            $rating->rateable->customer->updateRating();
        }
    }

    /**
     * Handle the Rating "updated" event.
     */
    public function updated(Rating $rating): void
    {
        if($rating->rateable_type == Response::class){
            $response = $rating->rateable;
            $request = $response->request;
            $seller = $response->seller;
            $seller->updateRating();
            $seller->notify(new UpdatedSellerRateNotification($request, $response, $request->country));
        }elseif ($rating->rateable_type == Request::class){
            $rating->rateable->customer->updateRating();
        }
    }

    /**
     * Handle the Rating "deleted" event.
     */
    public function deleted(Rating $rating): void
    {
        //
    }

    /**
     * Handle the Rating "restored" event.
     */
    public function restored(Rating $rating): void
    {
        //
    }

    /**
     * Handle the Rating "force deleted" event.
     */
    public function forceDeleted(Rating $rating): void
    {
        //
    }
}
