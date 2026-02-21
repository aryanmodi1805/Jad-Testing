<?php

namespace App\Jobs;

use App\Enums\Wallet\SubscriptionStatus;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpiredSubscription implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $now = Carbon::now();
        $allowedDays = filament()->getTenant()?->subscriptionAllowedDays ?? 0;
        $ends_time= $now->addDays($allowedDays);
       $items =  Subscription::where('ends_at', '<=', $ends_time->toDateTimeString())
            ->where('status', '=', SubscriptionStatus::ACTIVE)
            ->update(['status' => SubscriptionStatus::EXPIRED]);

    }
}
