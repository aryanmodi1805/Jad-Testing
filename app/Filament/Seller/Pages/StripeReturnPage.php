<?php

namespace App\Filament\Seller\Pages;

use App\Mail\GlobalMail as MailGlobalMail;
use App\Models\Package;
use App\Models\PaymentMethod;
use App\Models\Purchase;
use App\Services\Payment\ClickPay;
use App\Services\Payment\Stripe;
use Exception;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use O21\LaravelWallet\Models\Transaction;

class StripeReturnPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.seller.pages.stripe-return-page';

    protected static bool $shouldRegisterNavigation = false;
    public string $payment_status = 'unpaid';
    public string $complete_status = 'open';
    public bool $is_canceled = false;
    protected float $amount = 0.0;

    public function getTitle(): string|Htmlable
    {
        return __('Stripe confirm payment');
    }

    public function mount(Request $request): void
    {
        if ($request->input('tranRef')) {
            $this->updateCartByIPN($request);
        }
        $paymentMethod = PaymentMethod::where('type', Stripe::getProviderName())->firstOrFail();
        $payData = (new Stripe($paymentMethod->details))->successPayment($request->session_id);

        if (!empty($payData) && $payData['subscription_id'] == null) {
            $this->chargeBalance($payData, 100);
            $this->redirect(WalletPage::getUrl());
        } else
            $this->redirect(SubscriptionPlans::getUrl());


    }

    public function updateCartByIPN(Request $request)
    {
        $paymentMethod = PaymentMethod::where('type', ClickPay::getProviderName())->firstOrFail();
        $request_data = $request->all();


        $response = (new ClickPay($paymentMethod->details))->successPayment($request_data);

        $user = auth('seller')->user() ?? $response['user'] ?? null;
        if ($response && $response['status'] == true) {

            if (empty($response['subscription_id']) && (!empty($response['required_credit']) || $response['plan_id'])) {
                $charged = chargeCreditBalance(payData: $response, gatewayModel: ClickPay::class, payable: $user);
                if ($charged) {
                    Notification::make()
                        ->title(__('wallet.charge'))
                        ->body(__('wallet.added_to_balance', ['amount' => $charged]))
                        ->success()
                        ->persistent()
                        ->sendToDatabase($user)->send()
                        ->broadcast($user);


                    Mail::to($user->email)->queue(new MailGlobalMail(__('wallet.charge'), __('wallet.added_to_balance', ['amount' => $charged])));
                    return redirect()->to('/seller/wallet-page')->with('message', __('wallet.charge') . ':' . __('wallet.added_to_balance', ['amount' => $charged]));
                } else
                    Notification::make()
                        ->title(__('wallet.charge'))
                        ->body(__('wallet.failed_to_charge'))
                        ->danger()
                        ->persistent()
                        ->sendToDatabase($user)
                        ->send();

                return redirect('/seller/wallet-page')->withErrors(['message' => __('wallet.failed_to_charge')]);
            }
            if (!empty($response['subscription_id']))
                Notification::make()
                    ->title(__('subscriptions.subscription_success'))
                    ->body($request['message'])
                    ->success()
                    ->persistent()
                    ->send()
                    ->sendToDatabase($user);
            Mail::to($user->email)->queue(new MailGlobalMail(__('subscriptions.subscription_success'), $request['message']));
            return redirect('/seller/subscription-plans')->with('message', __('subscriptions.subscription_success'));
        } else
            Notification::make()
                ->title(__('subscriptions.subscription_failed'))
                ->body($request['message'])
                ->persistent()
                ->success()
                ->send()
                ->sendToDatabase($user);

        return redirect('/seller/subscription-plans')->withErrors(['message' => __('subscriptions.subscription_failed')]);

    }

    public function chargeBalance($payData, $divider = 1): void
    {
        try {
            $payable = auth('seller')->user();

            if (!empty($required_credit)) {

                $charged = chargeCreditBalance(payData: $payData, gatewayModel: Stripe::class, payable: $payable, divider: 100, payment_details_id: $payData['order_id']);

                $this->amount = $required_credit;
            }
            if (!empty($required_credit)) {
                Notification::make()
                    ->title(__('wallet.charge'))
                    ->body(__('wallet.added_to_balance', ['amount' => $required_credit]))
                    ->success()
                    ->persistent()
                    ->send();
            }

            $this->dispatch('refreshWallet');

        } catch (Exception $ex) {
            Notification::make()
                ->title(__('wallet.charge'))
                ->body($ex->getMessage())
                ->danger()
                ->persistent()
                ->send();


        }
    }

    protected function getHeaderWidgets(): array
    {
        return [


        ];
    }


}
