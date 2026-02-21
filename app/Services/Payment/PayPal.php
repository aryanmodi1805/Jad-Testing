<?php

namespace App\Services\Payment;

use App\Services\Payment\Concerns\KeysData;
use App\Services\Payment\Concerns\Payment;
use Illuminate\Support\Facades\Http;
use Stripe\StripeClient;

class PayPal extends Payment
{

    const url = '';

    public static function getProviderName(): string
    {
        return 'payPal';
    }


    public static function getKeysData(): array
    {
        return [
            KeysData::SecretKey => '',
            KeysData::PublishableKey => '',
            KeysData::WebhookKey => '',
        ];
    }

    public static function getName(): string
    {
        return PaymentService::paymentsList[static::getProviderName()];
    }

    public static function needOtp(): bool
    {
        return false;
    }

    public static function needReceiptNumber(): bool
    {
        return false;
    }

    public function createPayment($paymentMethodId = null, $custmer = null, $product, $amount = 0)
    {

    }

    public function createEmbeddedPayment($paymentMethodId = null, $custmer = null, $product, $amount = 0)
    {

    }

    public function checkOut($paymentMethodId, $customerPhone, $customerId, $amount)
    {

    }

    public function successPayment($paymentMethodId, $customerPhone, $customerId, $amount)
    {

    }

    public function cancelPayment($paymentMethodId, $customerPhone, $customerId, $amount)
    {

    }


    public function validate($data, $payment_detail_id)
    {

    }


}
