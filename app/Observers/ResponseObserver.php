<?php

namespace App\Observers;

use App\Enums\RequestStatus;
use App\Enums\ResponseStatus;
use App\Models\Response;
use App\Notifications\NewCreatedResponseNotification;
use App\Notifications\NewInvitationResponseNotification;
use App\Notifications\RequestStatusChangedNotification;
use App\Notifications\ResponseStatusChangedNotification;

class ResponseObserver
{
    /**
     * Handle the Response "created" event.
     */
    public function created(Response $response): void
    {
        if($response->status == ResponseStatus::Pending){
            $request = $response->request;
            $request->customer->notify(new NewCreatedResponseNotification($request, $response->seller, $request->country));

        }elseif($response->status == ResponseStatus::Invited){
            $request = $response->request;
            $response->seller->notify(new NewInvitationResponseNotification($request,$response, $request->customer, $request->country));
        }

    }

    /**
     * Handle the Response "updated" event.
     */
    public function updated(Response $response): void
    {
        if($response->getOriginal('status') != $response->status && $response->status  == ResponseStatus::Pending){
            $request = $response->request;
            $request->customer->notify(new NewCreatedResponseNotification($request, $response->seller, $request->country));

        }elseif ($response->getOriginal('status') != $response->status){
            $request = $response->request;
            $response->seller->notify(new ResponseStatusChangedNotification($request,$response, $request->country));
        }
    }

    /**
     * Handle the Response "deleted" event.
     */
    public function deleted(Response $response): void
    {
        //
    }

    /**
     * Handle the Response "restored" event.
     */
    public function restored(Response $response): void
    {
        //
    }

    /**
     * Handle the Response "force deleted" event.
     */
    public function forceDeleted(Response $response): void
    {
        //
    }
}
