<?php

namespace App\Filament\Wallet\Actions;

use App\Models\Package;
use App\Models\PaymentMethod;
use App\Services\Payment\PaymentService;
use Blade;
use Exception;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Illuminate\Support\HtmlString;

class ChargeCreditAction extends Action
{
    private float $credit_price = 0.0;

    public static function getDefaultName(): ?string
    {
        return 'Charge Credit';
    }

    public static function doChargeAction(array $data, $livewire, $credit_price, $action = 'charge', $actions = null): void
    {
        $user = auth(filament()->getAuthGuard())->user();
        $paymentMethod = PaymentMethod::find($data['payment'] ?? 2);
        $paymentClass = PaymentService::getPayment($paymentMethod->type);
        $required_credit = $data['credit'] ?? 0;

        $final_price = $credit_price * $required_credit;
        $final_price = round($final_price, 2);

        $gateway = new $paymentClass($paymentMethod->details);

        if ($final_price <= 0) {
            // Handle free purchase directly
            $user = auth(filament()->getAuthGuard())->user();
            $payment_detail_id = $gateway->newDetailPayment(
                $paymentMethod->id,
                0,
                getCountryId(),
                $user->id,
                0);

            $gateway->updateDetailPayment($payment_detail_id, 1, ['message' => 'Free credit']);

            // Credit the user's account
            chargeCreditBalance(
                payData: [
                    'product_id' => null,
                    'required_credit' => $required_credit,
                    'amount_total' => 0,
                    'trans_ref' => 'free_' . time(),
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

        // Check for existing pending payment
        $pendingPayment = \App\Models\PendingPayment::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'verifying'])
            ->where('payment_type', 'credit_charge')
            ->where('expires_at', '>', now())
            ->first();

        if ($pendingPayment) {
            \Filament\Notifications\Notification::make()
                ->title('Pending Payment Exists')
                ->body("You have a pending payment. Please wait " . $pendingPayment->secondsRemaining() . " seconds for verification.")
                ->warning()
                ->duration(5000)
                ->send();
            return;
        }

        $gateway->createPayment(
            paymentMethodId: $paymentMethod->id,
            customer: $user,
            product: null,
            amount: $final_price,
            country_id: getCountryId(),
            currency: 'SAR',
            required_credit: $required_credit,
            action: $action
        );
    }

    public static function getFormArray(string $currency, $payment_methods, $credit_price, $vat_percentage = 0): array
    {
        return [
            ToggleButtons::make('credit')
                ->label(__('wallet.enter_required_credits'))
                ->options([
                    50 => '50',
                    100 => '100',
                    150 => '150',
                    200 => '200',
                    300 => '300',
                    400 => '400',
                ])
                ->default(50)
                ->columns(2)
                ->validationAttribute(__('wallet.credits'))
                ->required()
                ->hint(__('wallet.one_credit_cost') . ': ' . $credit_price . ' ' . $currency)
                ->afterStateUpdated(function ($get, $set, $state) use ($currency, $credit_price) {
                    $final_price = $credit_price * $state;
                    $set('final_cost', $final_price . ' ' . $currency);
                    $set('price', round($final_price, 2));
                })
                ->live(debounce: 500),

            TextInput::make('final_cost')
                ->hint(new HtmlString(Blade::render('<x-filament::loading-indicator class="h-5 w-5" wire:loading wire:target="mountedActionsData.0.credit" />')))
                ->label(__('wallet.credit_cost'))
                ->prefix(__('wallet.credit_cost'))
                ->readOnly(),

            Placeholder::make('vat')
                ->content(__('wallet.packages.VAT', ['p' => $vat_percentage]))
                ->hiddenLabel(),

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
        $tenant = getCurrentTenant();
        $this->credit_price = $tenant?->credit_price ?? 1;
        // If credit_price is 0, use 1 as default
        if ($this->credit_price <= 0) {
            $this->credit_price = 1;
        }
        $currency = $tenant?->currency?->symbol ?? "";
        $vat_percentage = 0;

        $this->form(self::getFormArray($currency, $payment_methods, $this->credit_price, $vat_percentage));
        $this->icon('heroicon-o-calculator');

        $this->action(function (array $data, $livewire, $action): void {
            self::doChargeAction($data, $livewire, $this->credit_price, actions: $this);
            $livewire->dispatch('refreshWallet');
        })
        ->label(__('wallet.charge_credits'));
    }
}
