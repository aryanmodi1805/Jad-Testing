<?php

namespace App\Jobs;

use App\Enums\RequestStatus;
use App\Models\Request;
use App\Models\Seller;
use App\Notifications\NewRequestNotification;
use App\Services\RequestServiceHandler;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NewRequestNotificationJob implements ShouldQueue
{
    use Queueable;


    /**
     * Create a new job instance.
     */
    public function __construct(public Request $request)
    {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //recheck if the request is still open
        $this->request->refresh();

        if (!in_array($this->request->status, [RequestStatus::Open, RequestStatus::Booking])) {
            return;
        }

        $sellers = Seller::query()
            ->tenant($this->request->country_id)
            ->canServeRequest($this->request)->get();


        foreach ($sellers as $seller) {
            $seller->notify(new NewRequestNotification($this->request));
        }

    }
}
