<?php

namespace App\Filament\Wallet\Actions;

use App\Forms\Components\PackageSelectList;
use App\Models\Package;
use App\Models\PaymentMethod;
use App\Services\Payment\PaymentService;
use Blade;
use Exception;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;

class ChargePackageAction extends Action
{
    private float $credit_price = 0.0;
    
    public static function getDefaultName(): ?string
    {
        return 'Buy Package';
    }
    
    public static function doChargeAction(array $data, $livewire, $action = 'charge', $actions = null): void
    {
        $user = auth(filament()->getAuthGuard())->user();
        $package = Package::find($data['package_id']);
        $paymentMethod = PaymentMethod::find($data['payment'] ?? 2);
        $paymentClass = PaymentService::getPayment($paymentMethod->type);
        $required_credit = $package->credits ?? 0;
        $final_price = $package->getFinalPrice();
        
        if (!empty($data['coupon'])) {
            $code = $data['coupon'];
            try {
                $coupon = $package->verifyCoupon($code);
                $price_after_coupon = $coupon->calc(using: $package->getFinalPrice());
                $final_price = $price_after_coupon;
                $package->redeemCoupon($code, $user);
            } catch (Exception $e) {
                $livewire->js('alert('.json_encode($e->getMessage()).');');
                $actions->failure();
                $actions->failureNotificationTitle($e->getMessage());
                return;
            }
        }
        
        $gateway = new $paymentClass($paymentMethod->details);

        if ($final_price <= 0) {
            // Handle free purchase directly
            $payment_detail_id = $gateway->newDetailPayment(
                $paymentMethod->id,
                $package->id,
                getCountryId(),
                $user->id,
                0);

            $gateway->updateDetailPayment($payment_detail_id, 1, ['message' => 'Free package with coupon']);

            // Credit the user's account
            chargeCreditBalance(
                payData: [
                    'product_id' => $package->id,
                    'required_credit' => $required_credit,
                    'amount_total' => 0,
                    'trans_ref' => 'coupon_free_' . time(),
                    'payment_details_id' => $payment_detail_id
                ],
                gatewayModel: get_class($gateway),
                payable: $user,
                payment_details_id: $payment_detail_id,
                required_credit: $required_credit,
                tran_currency: 'SAR',
                country_id: getCountryId()
            );

            return;
        }
        
        $gateway->createPayment(
            paymentMethodId: $paymentMethod->id,
            customer: $user,
            product: $package,
            amount: $final_price,
            country_id: getCountryId(),
            currency: 'SAR',
            required_credit: $required_credit,
            action: $action
        );
    }
    
    public static function getFormArray(string $currency, $payment_methods): array
    {
        return [
            PackageSelectList::make('package_id')
                ->live()
                ->required()
                ->label(__('wallet.packages.select'))
                ->gridDirection('row')
                ->options(fn() => Package::tenant()->where('is_active', 1)->get()->pluck('name', 'id'))
                ->afterStateUpdated(function ($get, $set, $state) {
                    if (!empty($state)) {
                        $package = Package::find($state);
                        $set('price', $package->getFinalPrice());
                    }
                }),

            TextInput::make('coupon')
                ->label(__('wallet.discount_code'))
                ->suffixAction(
                    \Filament\Forms\Components\Actions\Action::make('applyCoupon')
                        ->icon('heroicon-m-percent-badge')
                        ->link()
                        ->extraAttributes([
                            'class' => 'btn-info w-full',
                        ])
                        ->label(__('wallet.verify_coupon'))
                        ->action(function ($get, $set, $state, TextInput $component) {
                            if (empty($get('package_id'))) {
                                $component->hint(__('wallet.packages.select'));
                                $component->hintColor('danger');
                                return;
                            }
                            if (empty($state)) {
                                $component->hint(__('wallet.enter_coupon'));
                                $component->hintColor('danger');
                                return;
                            }
                            $package = Package::find($get('package_id'));

                            try {
                                $code = $state;
                                $component->hintColor('gray-800');

                                $coupon = $package->verifyCoupon($code);

                                $coupon = $package->verifyCouponOr($code, function ($code, $exception) use ($set, $component, $package) {
                                    $overLimit = $package->isCouponOverLimit($code);

                                    if ($exception or $overLimit) {
                                        $component->hint(__('wallet.The coupon is invalid') . $exception->getMessage());
                                        $component->hintColor('danger');
                                    }
                                });
                                
                                if ($coupon) {
                                    $price_after_coupon = $coupon->calc(using: $package->getFinalPrice());
                                    $final_price = $price_after_coupon;
                                    $set('price', $final_price);
                                } else {
                                    return;
                                }
                            } catch (Exception $exception) {
                                $component->hint(__('wallet.The coupon is invalid') . $exception->getMessage());
                                $component->hintColor('danger');
                                $set('price', null);

                                Notification::make()
                                    ->title(__('wallet.apply_coupon'))
                                    ->body($exception->getMessage())
                                    ->danger()->send();
                            }
                        })
                ),

            TextInput::make('price')
                ->extraAttributes([
                    'class' => 'mx-auto',
                ])
                ->hiddenLabel()
                ->default(0)
                ->disabled()
                ->suffix($currency)
                ->dehydrated(false)
                ->prefix(__('wallet.packages.final_price')),

            Radio::make('payment')
                ->extraFieldWrapperAttributes([
                    'class' => '  mx-3',
                ])
                ->label(__('wallet.payments.select'))
                ->validationAttribute(__('wallet.payments.single'))
                ->inline()
                ->inlineLabel(false)
                ->options($payment_methods)->required(),
        ];
    }
    
    protected function setUp(): void
    {
        parent::setUp();
        $payment_methods = PaymentMethod::select('id', 'name', 'logo', 'type')->where('active', 1)->get()->pluck('name_html', 'id');
        $currency = Filament::getTenant()?->currency?->symbol ?? "";

        $this->form(self::getFormArray($currency, $payment_methods));
        $this->icon('heroicon-o-cube');

        $this->action(function (array $data, $livewire, $action): void {
            self::doChargeAction($data, $livewire, actions: $this);
            $livewire->dispatch('refreshWallet');
        })
        ->label(__('wallet.buy_package'));
    }
}
