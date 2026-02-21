<?php

namespace App\Observers;

use App\Enums\RequestStatus;
use App\Enums\ResponseStatus;
use App\Jobs\NewRequestNotificationJob;
use App\Models\Request;
use App\Notifications\RequestStatusChangedNotification;
use Illuminate\Support\Facades\Log;

class RequestObserver
{
    /**
     * Handle the Request "created" event.
     */
    public function created(Request $request): void
    {

    }

    /**
     * Handle the Request "updated" event.
     */
    public function updated(Request $request): void
    {
        if ($request->getOriginal('status') != $request->status) {
            $oldStatus = $request->getOriginal('status');
            $newStatus = $request->status;
            Log::info("RequestObserver: Request {$request->id} status changed from {$oldStatus->value} to {$newStatus->value}");

            // Auto-cancel pending/invited responses when request is completed or rejected
            if (in_array($newStatus, [RequestStatus::Completed, RequestStatus::Rejected])) {
                $responses = $request->responses()
                    ->whereIn('status', [ResponseStatus::Pending, ResponseStatus::Invited])
                    ->get();

                Log::info("RequestObserver: Found {$responses->count()} pending/invited responses to cancel for request {$request->id}");

                foreach ($responses as $response) {
                    $response->update(['status' => ResponseStatus::Cancelled]);
                    Log::info("RequestObserver: Cancelled response {$response->id} for seller {$response->seller_id}");

                    \Cache::forget("seller_responses_status_count_{$response->seller_id}_v1");
                }
            }

            // Restore cancelled responses back to Pending when request is reopened
            if (in_array($newStatus, [RequestStatus::Open, RequestStatus::Booking])) {
                $responses = $request->responses()
                    ->where('status', ResponseStatus::Cancelled)
                    ->get();

                Log::info("RequestObserver: Found {$responses->count()} cancelled responses to restore for request {$request->id}");

                foreach ($responses as $response) {
                    $response->update(['status' => ResponseStatus::Pending]);
                    Log::info("RequestObserver: Restored response {$response->id} to Pending for seller {$response->seller_id}");

                    \Cache::forget("seller_responses_status_count_{$response->seller_id}_v1");
                }
            }

            // Send notification (wrapped in try-catch so it doesn't block the observer)
            try {
                $request->customer->notify(new RequestStatusChangedNotification($request, $request->country));
            } catch (\Throwable $e) {
                Log::error("RequestObserver: Failed to send notification for request {$request->id}: {$e->getMessage()}");
            }

            // Dispatch new request notification job for open/booking statuses
            if (in_array($newStatus, [RequestStatus::Open, RequestStatus::Booking])) {
                NewRequestNotificationJob::dispatch($request)->delay(5000);
            }
        }
    }

    /**
     * Handle the Request "deleted" event.
     */
    public function deleted(Request $request): void
    {
        //
    }

    /**
     * Handle the Request "restored" event.
     */
    public function restored(Request $request): void
    {
        //
    }

    /**
     * Handle the Request "force deleted" event.
     */
    public function forceDeleted(Request $request): void
    {
        //
    }
}
