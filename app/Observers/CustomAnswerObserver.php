<?php

namespace App\Observers;

use App\Jobs\ReviewRequestAIJob;
use App\Models\CustomerAnswer;

class CustomAnswerObserver
{
    /**
     * Handle the CustomerAnswer "created" event.
     */
    public function created(CustomerAnswer $customerAnswer): void
    {
        if($customerAnswer->voice_note != null)
            ReviewRequestAIJob::dispatch($customerAnswer)->afterResponse();
    }

    /**
     * Handle the CustomerAnswer "updated" event.
     */
    public function updated(CustomerAnswer $customerAnswer): void
    {
        //
    }

    /**
     * Handle the CustomerAnswer "deleted" event.
     */
    public function deleted(CustomerAnswer $customerAnswer): void
    {
        //
    }

    /**
     * Handle the CustomerAnswer "restored" event.
     */
    public function restored(CustomerAnswer $customerAnswer): void
    {
        //
    }

    /**
     * Handle the CustomerAnswer "force deleted" event.
     */
    public function forceDeleted(CustomerAnswer $customerAnswer): void
    {
        //
    }
}
