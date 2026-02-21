<?php

namespace App\Models;

use App\Enums\Wallet\CreditType;
use App\Enums\Wallet\PremiumType;
use App\Enums\Wallet\SubscriptionStatus;
use App\Services\Payment\ClickPay;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $fillable = [

        'price_plan_id',
        'seller_id',
        'country_id',
        'status',
        'total_price',

        'is_premium',
        'is_auto_renew',
        'is_yearly',
        'is_monthly',
        'renew_at',
        'type',
        'stripe_id',
        'stripe_status',
        'stripe_price',
        'quantity',
        'trial_ends_at',
        'ends_at',
        'subscribe_at',
        'next_renew_at',
        'payment_method_id',
        'payment_details',
        'payment_status',
        'canceled_at',

        'is_in_credit',
        'premium_type',
        'credit_type',
        'premium_items_limit',
        'credit_items_limit',
        'renewal_trying',
        'trans_ref',
        'token',
        'agreement_id',

    ];

    // write all cast attributes
    protected $casts = [

        'subscribe_at' => 'datetime',
        'ends_at' => 'datetime',
        'renew_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'next_renew_at' => 'datetime',
        'canceled_at' => 'datetime',
        'total_price' => 'float',
        'payment_details' => 'json',
        'metadata' => 'json',

        'is_premium' => 'boolean',
        'is_auto_renew' => 'boolean',
        'is_yearly' => 'boolean',
        'is_monthly' => 'boolean',

        'premium_type' => PremiumType::class,
        'is_in_credit' => 'boolean',
        'credit_type' => CreditType::class,
        'status' => SubscriptionStatus::class,
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SubscriptionItem::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class, 'price_plan_id')->withTrashed();
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function scopeActive($query)
    {
        return $query->where(function ($query) {
            $query->where('ends_at', '>', Carbon::now())->where('status', 1)->whereNull('canceled_at');
        });
    }

    public function hasProduct($product)
    {
        return $this->items->contains(function (SubscriptionItem $item) use ($product) {
            return $item->stripe_product === $product;
        });
    }

    public function valid(): bool
    {
        return $this->active() || $this->onTrial();
    }

    public function active(): bool
    {
        return ! $this->isEnded() && ! $this->isCanceled() && $this->status == SubscriptionStatus::ACTIVE;

    }

    public function isEnded(): bool
    {
        return $this->ends_at->lessThan(Carbon::now());
    }

    public function isCanceled(): bool
    {
        return ! is_null($this->canceled_at);
    }

    public function renewable(): bool
    {
        return $this->isEnded() && $this->status != SubscriptionStatus::ACTIVE;

    }

    public function renew(): bool
    {
        // process payment s
        $this->renew_at = Carbon::now();
        $this->next_renew_at = null;
        $this->status = 1;
        $this->ends_at = Carbon::today()->addMonth();

        return $this->save();
    }

    public function refund($amount, $reason): bool
    {
        try {

            $paymentMethod = PaymentMethod::where('type', ClickPay::getProviderName())->firstOrFail();
            $cart_id = $this->payment_details?->cart_id ?? 'r_'.$this->id.'_'.Carbon::now()->format('Ymdhis');
            $refund = (new ClickPay($paymentMethod->details))->refundSubscription($this->trans_ref, $cart_id, $amount, $reason);

            $this->canceled_at = Carbon::now();
            $this->status = 4;

            return $this->save();
        } catch (Exception $exception) {
            return false;
        }
    }

    public function ended(): bool
    {
        $this->ends_at = Carbon::today();
        $this->status = 2;

        return $this->save();

    }

    public function scopeIncomplete($query)
    {
        return $query->where('status', 0);
    }

    public function canceling(): bool
    {
        // must update status to canceling on payment gateway

        if ($this->agreement_id) {
            $paymentMethod = PaymentMethod::where('type', ClickPay::getProviderName())->firstOrFail();
            $response = (new ClickPay($paymentMethod->details))->cancelSubscription($this->agreement_id);
        }

        $this->canceled_at = Carbon::now();
        $this->status = 2;

        return $this->save();

    }

    public function scopeCanceled($query)
    {
        return $query->whereNotNull('canceled_at');
    }

    public function scopeNotCanceled($query)
    {
        return $query->whereNull('canceled_at');
    }

    public function scopeOnGenericTrial($query)
    {
        return $query->whereNotNull('trial_ends_at')->where('trial_ends_at', '>', Carbon::now());
    }

    public function scopeHasExpiredGenericTrial($query)
    {
        return $query->whereNotNull('trial_ends_at')->where('trial_ends_at', '<', Carbon::now());
    }

    public function scopeExpired($query)
    {
        return
            $query->where(function ($query) {
                $query->where('ends_at', '<=', Carbon::now()->toDateTimeString())
                    ->orWhere('status', SubscriptionStatus::EXPIRED);
            })
                ->whereNull('canceled_at');
    }

    public function trialEndsAt($type = 'default'): ?\Illuminate\Support\Carbon
    {
        if (func_num_args() === 0 && $this->onGenericTrial()) {
            return $this->trial_ends_at;
        }

        if ($subscription = $this->subscription($type)) {
            return $subscription->trial_ends_at;
        }

        return $this->trial_ends_at;
    }
}
