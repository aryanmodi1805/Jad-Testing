<?php

namespace App\Traits\Wallet;

use App\Enums\Wallet\CreditType;
use App\Enums\Wallet\PremiumType;
use App\Enums\Wallet\SubscriptionPlanType;
use App\Models\PricingPlan;
use App\Models\Service;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasSubscription
{
    public function newSubscription(array $data, $price_plan_id): ?Subscription
    {
        $plan = PricingPlan::find($price_plan_id);
        $now = Carbon::now();

        $subscription = Subscription::create([
            'price_plan_id' => $plan->id,
            'seller_id' => $this->id,
            'total_price' => $data['total_price'] ?? $plan->month_price,
            'is_premium' => $plan->is_premium ?? ($data['premium'] ?? false),
            'is_in_credit' => $plan->is_in_credit ?? ($data['credit'] ?? false),
            'premium_type' => $plan->premium_type ?? null,
            'credit_type' => $plan->credit_type ?? null,
            'premium_items_limit' => $plan->premium_items_limit ?? null,
            'credit_items_limit' => $plan->credit_items_limit ?? null,
            'is_auto_renew' => $data['is_auto_renew'] ?? false,
            'is_yearly' => $plan->billing_cycles==1,
            'is_monthly' => $plan->billing_cycles==0,
            'renew_at' => $plan->billing_cycles==1 ? $now->copy()->addYear() : $now->copy()->addMonth(),
            'stripe_price' => $data['total_price'] ?? $plan->month_price,
            'quantity' => $data['quantity'] ?? 1,
            'trial_ends_at' => $plan->trial_days ? $now->copy()->addDays($plan->trial_days) : $now,
            'ends_at' =>$plan->billing_cycles==1 ? $now->copy()->addYear() : $now->copy()->addMonth(),
            'subscribe_at' => $now,
            'next_renew_at' =>$plan->billing_cycles==1 ? $now->copy()->addYear() : $now->copy()->addMonth(),
            'country_id' => getCurrentTenant()?->id ?? $data['country_id'] ?? null,
        ]);

        // Prepare batch insert data
        $items = [];
        foreach (['premium' => PremiumType::class, 'credit' => CreditType::class] as $type => $enumClass) {
            $typeValue = SubscriptionPlanType::from($type)->value;
            foreach ($enumClass::cases() as $enum) {
                $column = $enum->getColumnName();
                foreach ($data[$typeValue][$column] ?? [] as $id) {
                    $items[] = [
                        'subscription_id' => $subscription->id,
                        $column => $id,
                        'quantity' => 1,
                        'type' => $typeValue,
                    ];
                }
            }
        }

        if (!empty($items)) {
            $subscription->items()->createMany($items);
        }

        return $subscription;

    }

    /**
     * Determine if the model has a given subscription.
     */
    public function subscribed($plan_id = null, PricingPlan $service = null, $main_category = null, $sub_category = null, $type = 'default'): bool
    {
        // check if seller have active subscription or have active subscription
        if ($plan_id)
            return $this->subscribedToPlan($plan_id);


        if ($service) return $this->subscribedToService($service, $main_category, $sub_category, $type);

//        if($main_category) return $this->subscribedToMainCategory($main_category, $type);


        return false;
    }

    public function subscribedToPlan($price_plan_id): bool
    {
        return $this->subscriptions()->active()->where('price_plan_id', $price_plan_id)->exists();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscribedToService($service_id, $main_category = null, $sub_category = null, $type = 'default')
    {

        $service = Service::with('category')->find($service_id);
        $service_id = $service->id ?? -1;
        $sub_category_id = $service->category_id ?? $sub_category ?? -1;
        $main_category_id = $service->category?->parent_id ?? $main_category ?? -1;
        return $this->subscriptions()
            ->active()
            ->where(fn($query) => $query->where('is_in_credit', true)
                ->where(fn($query) => $query->Where('credit_items_limit', '=', -1)
                    ->orWhereHas('items', function ($query) use ($service_id, $main_category_id, $sub_category_id) {
                        $query->where('type', SubscriptionPlanType::CREDIT)
                            ->where('main_category_id', $main_category_id)
                            ->orWhere(function ($query) use ($service_id, $sub_category_id) {
                                $query->where('sub_category_id', $sub_category_id)
                                    ->orWhere('service_id', $service_id);
                            });
                    })
                )
            );

    }

    /**
     * Determine if the   model is actively subscribed to one of the given Services.
     */
    public function isSubscribedToService($service_id, $main_category = null, $sub_category = null, $type = 'default'): bool
    {
        return $this->subscribedToService($service_id, $main_category, $sub_category, $type)->exists();
    }

    /**
     * Scope a query to determine if the model is actively subscribed to one of the given Services.
     */
    public function scopeSubscribedToService($query, $service, $main_category = null, $sub_category = null, $type = 'default')
    {
        return $query;
    }

    /**
     * Get a subscription instance by $type.
     */
    public function subscription($type = 'default')
    {
        return $this->subscriptions->where('type', $type)->first();
    }

    public function activeSubscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class)->active();
    }

    public function hasIncompletePayment($type = 'default'): bool
    {
        return false;
    }


}
