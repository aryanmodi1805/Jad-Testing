<?php

namespace App\Services\Payment;

use App\Filament\Seller\Pages\StripeReturnPage;
use App\Models\Subscription;
use App\Services\Payment\Concerns\KeysData;
use App\Services\Payment\Concerns\Payment;
use Illuminate\Support\HtmlString;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class Stripe extends Payment
{

    const url = '';

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

    public static function getProviderName(): string
    {
        return 'stripe';
    }

    public static function needOtp(): bool
    {
        return false;
    }

    public static function needReceiptNumber(): bool
    {
        return false;
    }

    public function CreatePaymentIntent($paymentMethodId,
                                        $customerId,
                                        $product,
                                        $amount = 0,
                                        $currency = 'usd',
                                        $country_id , $action = null)
    {
        $stripe = new StripeClient($this->data[KeysData::SecretKey]);

        $payment_detail_id = $this->newDetailPayment($paymentMethodId, $customerId, $country_id);
        $stripe->setupIntents->create(['payment_method_types' => ['card']]);

        return $stripe->paymentIntents->create([
            'amount' => $amount * 100,
            'currency' => $currency,
//            'customer' => auth()->user()->name,
//            'confirm' => false,
            'use_stripe_sdk' => true,
            'payment_method_options' => ['card' => ['capture_method' => 'manual']],
//            'setup_future_usage' => 'off_session',
//            'payment_method' => '{{CARD_ID}}',


            'description' => new HtmlString($product->description . '<br>' . $product->name),
//            'payment_method_types' => ['card'],
//            'statement_descriptor' => 'Custom descriptor',
            'automatic_payment_methods' => ['enabled' => true, 'allow_redirects' => 'never'],
            'metadata' => ['product_id' => $product->id, 'order_id' => $payment_detail_id],
        ]);
    }

    public function createPayment($paymentMethodId, $customer, $product, $amount, $currency, $country_id, $required_credit=0, $subscription = null, $action = null)
    {
        $stripe = new StripeClient($this->data[KeysData::SecretKey]);
        $product_data = $product ? ['name' => $product->getWalletMeta()['data']] :
            ['name' => __('wallet.buy_credit') . '(' . $required_credit . ')' . __('wallet.credits'), 'unit_label' => $required_credit];
        $price_data = [
            'currency' => $currency,
            'unit_amount' => $amount * 100,
            'product_data' => $product_data,
        ];
//        if ($subscription) {
//            $price_data['recurring'] = ['interval' => 'month'];
//        }

        $price = $stripe->prices->create($price_data);
        $payment_detail_id = $this->newDetailPayment($paymentMethodId, $product->id ?? 0, $country_id, auth(filament()->getAuthGuard())->user()->id, $amount);
        $checkout_session = $stripe->checkout->sessions->create([

                'metadata' => [
                    'product_id' => $product?->id ?? 0,
                    'order_id' => $payment_detail_id,
                    'required_credit' => $required_credit ?? 0,
                    'subscription_id' => $subscription?->id ?? null,
                    'paymentMethod_id' => $paymentMethodId ?? null,
                ],
                'line_items' => [
                    [
                        'price' => $price->id,
                        'quantity' => 1,
                    ],
                ],
//                'amount_total' => $amount,
                'customer_email' => auth(filament()->getAuthGuard())->user()->email,
                'locale' => 'auto',
                'mode' => 'payment',
                'submit_type' => 'pay',
//                'payment_method_types' => ['card', 'paypal'],
//            'ui_mode' => 'embedded',
//                'return_url' => route('stripe.return'),
                'success_url' => StripeReturnPage::getUrl() . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => StripeReturnPage::getUrl() . '?session_id={CHECKOUT_SESSION_ID}',
            ]
        );

        return redirect()->to($checkout_session->url);
    }

    public function successPayment($session_id)
    {
        $session_response = json_decode(json_encode($this->retrieveSession($session_id)));
        if ($session_response->payment_status == "paid") {
            $this->updateDetailPayment($session_response->metadata->order_id, 1, $session_response);
            if ($session_response->metadata->subscription_id) {
                Subscription::find($session_response->metadata->subscription_id)->update(
                    ['status' => 1,
                        'payment_method_id' => $session_response->metadata->paymentMethod_id,
                        'payment_details' => $session_response,
                        'payment_status' => 1,]);
            }
            return [
                'product_id' => $session_response->metadata->product_id,
                'order_id' => $session_response->metadata->order_id,
                'amount_total' => $session_response->amount_total,
                'required_credit' => $session_response->metadata->required_credit,
                'subscription_id' => $session_response->metadata->subscription_id,
                'payment_details_id' =>  $session_response->metadata->order_id,
            ];
        } else {
            $this->updateDetailPayment($session_response->metadata->order_id, 0, $session_response);
            return null;
        }

    }

    public function retrieveSession($session_id): ?Session
    {
        $stripe = new StripeClient($this->data[KeysData::SecretKey]);
        try {
            return $stripe->checkout->sessions->retrieve($session_id, []);
        } catch (ApiErrorException $e) {
        }
        return null;
    }

    public function cancelPayment($session_id)
    {
        $session_response = json_decode(json_encode($this->retrieveSession($session_id)));
        $this->updateDetailPayment($session_response->metadata->order_id, 0, $session_response);
        return $session_response->metadata->product_id;
    }

    public function validate($data, $payment_detail_id)
    {
    }

    public function createEmbeddedPayment($paymentMethodId = null, $custmer = null, $product, $amount = 0)
    {


        $stripe = new StripeClient($this->data[KeysData::SecretKey]);

        $price = $stripe->prices->create([
            'currency' => 'usd',
            'unit_amount' => $amount * 100,
            //  'recurring' => ['interval' => 'month'],
            'product_data' => ['name' => $product->name . $product->description],
        ]);

        $checkout_session = $stripe->checkout->sessions->create(
            [
                'line_items' => [
                    [
                        'price' => $price->id,
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'ui_mode' => 'embedded',
                'return_url' => route('stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
            ]
        );
//        header("HTTP/1.1 303 See Other");
//        header("Location: " . $checkout_session->url);
        return redirect()->to($checkout_session->url);


    }

    public function checkOut($paymentMethodId, $customerPhone, $customerId, $amount)
    {

    }

}
