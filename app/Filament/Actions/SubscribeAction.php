<?php

namespace App\Filament\Actions;

use App\Concerns\SubscribeFrom;
use App\Enums\Wallet\SubscriptionPlanType;
use App\Models\PaymentMethod;
use App\Models\PricingPlan;
use App\Services\Payment\PaymentService;
use Filament\Tables\Actions\Action;
use Illuminate\Support\HtmlString;


class SubscribeAction extends Action
{
//    protected Model|Closure|null $record = null;
    public PricingPlan|null $plan = null;
    public bool $isAlreadySubscribed = false;

    public static function getDefaultName(): ?string
    {
        return 'subscribe';
    }


    protected function setUp(): void
    {
        parent::setUp();
        $this->icon('heroicon-o-chevron-left')
            ->mountUsing(function (array $arguments, $action) {
                $plan = $this->getRecord();
//                $this->setPlan($plan);
                if (auth(filament()->getAuthGuard())->user()->subscribedToPlan($plan->id)) {
                    $this->setIsAlreadySubscribed(true);
                 $action->failureNotificationTitle(__('subscriptions.already_subscribed'));
                    $action->failure();
                    $action->cancel(true);

                }
            })
            ->modalHeading(fn() =>
            new HtmlString(
                __('subscriptions.subscribe_now') . ($this->getRecord() != null ? (' - ' . $this->getRecord()->name).'( '. $this->getRecord()->getFinalPrice() .$this->getRecord()->currency?->symbol.')' : '') .
                ( $this->isAlreadySubscribed ? '<span class="text-xs bg-green-100 mx-3 my-0  px-1 transition">' . __("subscriptions.already_subscribed") . '</span>' :"" )
            ))
            ->modalDescription(function ($record) {
                $html = '';
                if ($record->is_premium && $this->isAlreadySubscribed) {
                    $subscriptions = auth('seller')->user()->subscriptions()->active()->where('price_plan_id', $record->id)
                        ->with(['items' => function ($query) {
                            $query->where('type', SubscriptionPlanType::PREMIUM);
                        }])
                        ->get();
                    $html.="<span class='font-semibold'>".SubscriptionPlanType::PREMIUM->getLabel() ."</span> ";
                    foreach ($subscriptions as $subscription) {
                        $html.=" : (# $subscription->id ) " . $subscription->premium_type->getLabel() . " : ";
                        foreach ($subscription->items as $service) {
                            $html .= '<span class="bg-yellow-100 text-yellow-800 text-xs font-medium me-2 px-1  py-0 rounded dark:bg-gray-700 dark:text-yellow-300 border border-yellow-300">'
                                . $service->getName($subscription->premium_type)
                                . '</span >';
                        }
                    }
                }
                if ($record->is_in_credit && $this->isAlreadySubscribed) {
                    $subscriptions = auth('seller')->user()->subscriptions()->active()->where('price_plan_id', $record->id)
                        ->with(['items' => function ($query) {
                            $query->where('type', SubscriptionPlanType::CREDIT);
                        }])
                        ->get();
                    $html.="<br><br> <span class='  font-semibold'>". SubscriptionPlanType::CREDIT->getLabel() ." : </span> ";
                    foreach ($subscriptions as $subscription) {
                        $html.=" : (# $subscription->id ) " . $subscription->credit_type->getLabel() ." : ";
                        foreach ($subscription->items as $service) {
                            $html .= '<span class="bg-green-100 text-green-800 text-xs font-medium me-2 px-1  py-0  rounded dark:bg-gray-700 dark:text-green-400 border border-green-400">'
                                . $service->getName($subscription->credit_type)
                                . '</span >';
                        }
                    }
                }



                return new HtmlString($html);
            })
            ->outlined()
            ->modalSubmitActionLabel(__('subscriptions.subscribe_now'))
            ->form(function ($livewire) {
                $plan = $this->getRecord();
                $creditType = SubscriptionPlanType::CREDIT->value;
                $premiumType = SubscriptionPlanType::PREMIUM->value;
                if (!$plan)
                    return [];

                if ($this->isAlreadySubscribed()) return [];

                if ($plan->is_premium) {
                    $premiumKey = $plan->premium_type?->getColumnName();
                    if ($premiumKey) {
                        $livewire->mountedTableActionsData[0][$premiumType][$premiumKey] ??= [];
                    }
                }

                if ($plan->is_in_credit) {
                    $creditKey = $plan->credit_type?->getColumnName();
                    if ($creditKey) {
                        $livewire->mountedTableActionsData[0][$creditType][$creditKey] ??= [];
                    }
                }
                return SubscribeFrom::getForm($plan, $premiumType, $creditType);
            })
            ->extraAttributes(['class' => ' rounded-md  bg-white px-6 py-2  min-w-[240px] mb-4  -mt-20   shadow-2xl  text-primary-600', 'style' => app()->getLocale() == 'en' ? 'margin-left:13%;' : 'margin-right:10%;'])
            ->action(function (array $data, $action, $livewire) {

                $plan = $this->getRecord();
                $action_type = $livewire->action_type ?? 'new';

                if ($this->isAlreadySubscribed()) {
                    $action->failure();
                    $action->failureNotificationTitle(__('subscriptions.already_subscribed'));
                    return;
                }

                $newSubscription = auth(filament()->getAuthGuard())->user()->newSubscription($data, $plan->id);

                $user = auth(filament()->getAuthGuard())->user();
                $package = PricingPlan::find($plan->id);
                $paymentMethod = PaymentMethod::find($data['payment'] ?? 2);
                $paymentClass = PaymentService::getPayment($paymentMethod->type);
                $final_price = $package->getFinalPrice();


                $paymentGateway = new $paymentClass($paymentMethod->details);

                $url = $paymentGateway->createPayment(
                    paymentMethodId: $paymentMethod->id,
                    customer: $user,
                    product: $package,
                    amount: $final_price,

                    country_id: getCountryId(),
                    currency: 'SAR',
                    subscription: $newSubscription,
                    action: $action_type,
                );


                $action->successNotificationTitle(__('subscriptions.subscription_success'));


            })
            ->label(__('subscriptions.subscribe_now'));

    }

    public function isAlreadySubscribed(): bool
    {
        return $this->isAlreadySubscribed;
    }

    public function setIsAlreadySubscribed(bool $isAlreadySubscribed): void
    {
        $this->isAlreadySubscribed = $isAlreadySubscribed;
    }

//    public function getPlan(): ?PricingPlan
//    {
//        return $this->plan;
//    }

//    public function setPlan(?PricingPlan $plan): void
//    {
//        $this->plan = $plan;
//    }

}
