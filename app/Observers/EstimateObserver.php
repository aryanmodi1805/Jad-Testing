<?php

namespace App\Observers;

use App\Events\MessageReadEvent;
use App\Models\Estimate;
use App\Notifications\NewEstimateNotification;
use Filament\Facades\Filament;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;

class EstimateObserver
{
    /**
     * Handle the Request "created" event.
     */
    public function created(Estimate $estimate): void
    {
        $response = $estimate->response;
        $request = $response->request;
        $request->customer->notify(new NewEstimateNotification($estimate , $request , from: $response->seller, tenant:  getCurrentTenant()));
    }

    /**
     * Handle the Request "updated" event.
     */
    public function updated(Estimate $estimate): void
    {
        $response = $estimate->response;
        $request = $response->request;
        $request->customer->notify(new NewEstimateNotification($estimate , $request , from: $response->seller, tenant:  getCurrentTenant()));


    }

    /**
     * Handle the Request "deleted" event.
     */
    public function deleted(Estimate $estimate): void
    {

    }

    /**
     * Handle the Request "restored" event.
     */
    public function restored(Estimate $estimate): void
    {

    }

    /**
     * Handle the Request "force deleted" event.
     */
    public function forceDeleted(Estimate $estimate): void
    {

    }
}
