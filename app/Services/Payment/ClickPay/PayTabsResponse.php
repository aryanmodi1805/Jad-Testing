<?php

namespace App\Services\Payment\ClickPay;

use App\Models\PaymentMethod;
use App\Models\Purchase;
use App\Services\Payment\ClickPay;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;

class PayTabsResponse
{
    public static function updateCartByIPN(Request $request)
    {
        $paymentMethod = PaymentMethod::where('type', ClickPay::getProviderName())->firstOrFail();
        $requestData = $request->all();
        $clickPay = new ClickPay($paymentMethod->details);
        $response = $clickPay->successPayment($requestData, $paymentMethod->id);
        $user = auth('seller')->user() ?? $response['user'] ?? null;
        $isPurchasedBefore = Purchase::where('transaction_id', $response['trans_ref'])->exists();
        if ($response && $response['status'] ) {
            if (empty($response['subscription_id']) && (!empty($response['required_credit']) || $response['plan_id']) && !$isPurchasedBefore) {
                return self::handleCreditCharge($response, $user, $requestData);
            }
            if (!empty($response['subscription_id'])) {
                return self::handleSubscriptionSuccess($response, $user);
            }
        }

        return self::handleFailure($response, $request, $user);
    }

    private static function handleCreditCharge(array $response, $user, array $requestData)
    {
        $charged = chargeCreditBalance(
            payData: $response,
            gatewayModel: ClickPay::class,
            payable: $user,
            payment_details_id: $response['payment_details_id'],
            required_credit: $response['required_credit'],
            tran_currency: $response['tran_currency'] ?? $requestData['tran_currency']
        );
        if ($charged) {
            if($response['action'] =='api')
            {
               return redirect(route('payment.success'));
            }
            self::sendNotification(__('wallet.payment_status.success'), $user, __('wallet.added_to_balance', ['amount' => $charged]));
            return redirect()->to('/seller/wallet-page?cr='.$charged.'&e=0')->with('message', __('wallet.charge') . ':' . __('wallet.added_to_balance', ['amount' => $charged]));
        }
        if($response['action'] =='api')
        {
            return redirect(route('payment.failure'));
        }
        self::sendNotification(__('wallet.failed_to_charge'), $user, __('wallet.charge'));
        return  redirect()->to('/seller/wallet-page?s=0&e=1')->withErrors(['message' => __('wallet.failed_to_charge')]);
    }

    public static function sendNotification(string $message, $user, string $title = '',$success =false): void
    {
        Notification::make()
            ->title($title)
            ->body($message)
            ->persistent()
            ->success()
            ->sendToDatabase($user)
            ->send();
    }

    private static function handleSubscriptionSuccess(array $response, $user)
    {
        if($response['action'] =='api')
        {
            return redirect(route('payment.success'));
        }
        self::sendNotification($response['message'], $user, __('subscriptions.subscription_success'));
        return  redirect()->to('/seller/settings/subscriptions')->with('message', __('subscriptions.subscription_success'));
    }

    private static function handleFailure(array $response, Request $request, $user)
    {
        if($response['action'] =='api')
        {
            return redirect(route('payment.failure'));
        }
        self::sendNotification($response['message'], $user, __('wallet.payment_status.failed'));
        return !empty($response['subscription_id'])
            ?  redirect()->to('/seller/subscription-plans?s=1&e=1&status='.$response['message'])->with('message', __('subscriptions.subscription_failed'))
            :  redirect()->to('/seller/wallet-page?s=0&e=1&status='.$response['message'])->with('message',('wallet.failed_to_charge'));
    }
}
