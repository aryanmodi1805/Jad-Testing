<?php

namespace App\Jobs;

use App\Services\Payment\ClickPay;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class RenewSubscriptionJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    protected $clickpay;

    /**
     * Create a new job instance.
     */
    public function __construct(ClickPay $clickpay)
    {
        $this->clickpay = $clickpay;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $now = Carbon::now();

        // Get all expired subscriptions
        $subscriptions = DB::table('subscriptions')
            ->with(['seller', 'plan'])
            ->where('ends_at', '<', $now)
            ->where('is_auto_renew', 1)
            ->where('status', 1)
            ->whereNull('canceled_at')
            ->where('renewal_trying', '<', 4)
            ->where(fn($query) => $query->whereNull('next_renew_at')->orWhereDate('next_renew_at', '!=', Carbon::today()->toDateString()))
            ->get();

        foreach ($subscriptions as $subscription) {
            // Make payment check if is successful renew subscription
            $paymentSuccess = $this->processPayment($subscription,$subscription->token,$subscription->trans_ref);

            if ($paymentSuccess) {
                $subscription->renew();

            } else {
                DB::table('subscriptions')
                    ->where('id', $subscription->id)
                    ->update([
                        'renewal_trying' => $subscription->renewal_trying + 1,
                        'next_renew_at' => $now->addDay(),
                        'updated_at' => $now,

                    ]);
            }
        }
    }

    private function processPayment($subscription, $token, $tran_ref): bool
    {
        $pay = null;

        $cart =[
            'cart_id' => ($subscription->price_plan_id ?? '102') . '_' . date('Ymdhis'),
            'amount' => (float)$subscription->total_price,
            'cart_description' =>  __('subscriptions.renew_subscription'),
        ];
        try {
            $pay=  $this->clickpay->recurringPaySubscription($token,$tran_ref,...$cart);

            return $pay && $pay->payment_result?->response_status === 'A';
        } catch (Exception $e) {
            // Handle payment failure
            return false;
        }


    }

}
