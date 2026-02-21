<?php

namespace App\Services\Payment;

use App\Concerns\SubscribeFrom;
use App\Models\Purchase;
use App\Models\Seller;
use App\Models\Subscription;
use App\Services\Payment\Concerns\KeysData;
use App\Services\Payment\Concerns\Payment;
use Clickpaysa\Laravel_package\Facades\paypage;
use Config;
use Exception;
use Filament\Notifications\Notification;
use GuzzleHttp\Client;
use Str;


class ClickPay extends Payment
{

    const url = 'https://secure.clickpay.com.sa/';

    public static function getKeysData(): array
    {
        return [
            KeysData::ServerKey => 'SRJNMBTMKH-JJR29KMD9D-WLHHLBBJWR',
            KeysData::ClientKey => 'CBKMTN-6P2766-RMPKND-9MRHVT',
            KeysData::MerchantID => '45243',
            KeysData::MethodKey => '',
        ];
    }

    public static function getName(): string
    {
        return PaymentService::paymentsList[static::getProviderName()];
    }

    public static function getProviderName(): string
    {
        return 'clickPay';
    }

    public static function needOtp(): bool
    {
        return false;
    }

    public static function needReceiptNumber(): bool
    {
        return false;
    }

    public function createPayment(
        $paymentMethodId,
        $customer,
        $product,
        $amount,
        $country_id,
        $currency = 'SAR',
        $required_credit = 0,
        $subscription = null,
        $action = null)
    {
        // Check if amount is 0 after coupon application
        if ((float)$amount <= 0) {
            // Create payment detail with successful status
            $payment_detail_id = $this->newDetailPayment(
                $paymentMethodId,
                $product->id ?? 0,
                $country_id,
                auth(filament()->getAuthGuard())->user()->id,
                0);

            // Update the payment detail as successful
            $this->updateDetailPayment($payment_detail_id, 1, ['message' => 'Free credit with coupon']);

            // Return success data for handling in successPayment method
            return [
                'success' => true,
                'tran_ref' => 'coupon_free_' . time(),
                'payment_detail_id' => $payment_detail_id,
                'required_credit' => $required_credit,
                'user_defined' => json_encode([
                    'payment_detail_id' => $payment_detail_id,
                    'plan_id' => $product->id ?? null,
                    'required_credit' => $required_credit,
                    'user_px' => $customer->id,
                    'action' => $action
                ])
            ];
        }

        // Original code for positive amounts
        $cart = [
            'cart_id' => ($product?->id ?? '102') . '_' . date('Ymdhis'),
            'amount' => (float)$amount,
            'cart_description' => $product?->getWalletMeta()['data'] ?? __('wallet.buy_credit') . '(' . $required_credit . ')' . __('wallet.credits'),
        ];


        $customer_data = [
            'name' =>  trim($customer->name)??'',
            'email' => trim($customer->email)??"",
            'phone' =>  trim($customer->phone) ?? '',
            'address' => $customer->address ?? 'street1',
            'city' => $customer->city ?? "city",
            'state' => $customer->state ?? "riyadh",
            'country' => $customer->country?->slug ?? "SA",
            'zip' => $customer->zip ?? null,
            'ip' => $customer->ip ?? null,
        ];

        $agreement = [];
        /*   if ($subscription)
               $agreement = [
                   "agreement_description" => $customer->name . " agreement",
                   "agreement_currency" => $currency,
                   "initial_amount" => $amount,
                   "repeat_amount" => $amount,
   //                "final_amount" => $amount,
                   "repeat_terms" => 0,
                   "repeat_period" => 3,
                   "repeat_every" => 1,
                   "first_installment_due_date" => $subscription?->ends_at?->format('d/m/Y') ?? Carbon::today()->addMonth()->format('d/m/Y'),
               ];*/

        $payment_detail_id = $this->newDetailPayment(
            $paymentMethodId,
            $product->id ?? 0,
            $country_id,
            auth(filament()->getAuthGuard())->user()->id,
            $amount);
        Config::set('clickpay.profile_id', $this->data[KeysData::MerchantID]);
        Config::set('clickpay.server_key', $this->data[KeysData::ServerKey]);
        Config::set('clickpay.currency', Str::upper($currency) ?? $currency);

        try {
            $pay = paypage::sendPaymentCode($this->data[KeysData::MethodKey] ?? 'all')
                ->sendTransaction('sale', 'ecom')
//                ->sendTransaction('sale', $subscription ? 'recurring' : 'ecom')
                //            ->capture('tran_ref','order_id','amount','capture description')
                ->sendCart(...$cart)
                ->sendCustomerDetails(...$customer_data)
                ->sendTokinse(true)
                ->sendUserDefined(
                    [
                        'udf1' => [
                            "payment_detail_id" => $payment_detail_id ?? null,
                            "subscription_id" => $subscription->id ?? null,
                            "plan_id" => $product->id ?? null,
                            "required_credit" => $required_credit ?? "0",
                            "user_px" => $customer->id ?? null,
                            "action" => $action ?? null,

                        ]
                    ]
                )
                ->sendHideShipping(true)
                ->sendURLs(route('payment_ipn'), Config::get('clickpay.callback_url'))
                ->sendLanguage(app()->getLocale() ?? 'ar');
            if ($agreement)
                $pay->sendaAgreementDetails($agreement);

            return $pay->create_pay_page();

        } catch (Exception $e) {

            Notification::make()->body($e->getMessage())->danger()->send();

        }
        return null;

    }


    public function successPayment($request_data, $paymentMethodId = 0)
    {
        Config::set('clickpay.profile_id', $this->data[KeysData::MerchantID]);
        Config::set('clickpay.server_key', $this->data[KeysData::ServerKey]);

        $transaction = paypage::queryTransaction($request_data['tranRef'] ?? $request_data['tran_ref']);
        $session_response = json_decode(json_encode($transaction));


        $user_defined = json_decode(reset($transaction->user_defined)); //  dd($request_data, $session_response, $user_defined);
        $return_data = [
            'product_id' => $user_defined->plan_id ?? null,
            'order_id' => $user_defined->payment_detail_id ?? null,
            'amount_total' => $transaction->tran_total ?? 0,
            'required_credit' => $user_defined->required_credit ?? 0,
            'subscription_id' => $user_defined->subscription_id ?? null,
            'user' => auth('seller')->user() ?? Seller::find($user_defined->user_px) ?? null,
            'status' => true,
            'message' => $transaction->message ?? 'Payment success',
            'payment_details_id' => $user_defined->payment_detail_id,
            'trans_ref' => $transaction->transaction_id ?? $request_data['tranRef'] ?? $transaction->tran_ref ?? null,
            'tran_currency' => $transaction->tran_currency ?? null,
            'action' => $user_defined->action?? null,

        ];
        if ($transaction->success === true) {

            $this->updateDetailPayment($user_defined->payment_detail_id, 1, $session_response);
            if ($user_defined->subscription_id) {
                $subscription = Subscription::find($user_defined->subscription_id);
                $subscription->update(
                    ['status' => 1,
                        'payment_method_id' => $paymentMethodId,
                        'payment_details' => $session_response,
                        'payment_status' => 1,
                        'trans_ref' => $transaction->transaction_id ?? $request_data['tranRef'] ?? $transaction->tran_ref ?? null,
                        'token' => $request_data['token'] ?? $session_response->token ?? null,
                        'agreement_id' => $request_data['agreement_id'] ?? $session_response->agreement_id ?? null,
                    ]);
                if ($user_defined->action == 'renew') {
                    $subscription->renew();
                }
                $payable = auth('seller')->user() ?? Seller::find($user_defined->user_px) ?? null;
                $item = $subscription->plan;
                SubscribeFrom::createPurchase(
                    price: $transaction->tran_total,
                    item: $item,
                    payment: $this::getProviderName(),
                    payable: $payable,
                    transaction_id: $transaction->transaction_id ?? $request_data['tranRef'] ?? $transaction->tran_ref ?? null,
                    chargeable: $subscription,
                    payment_detail_id: $user_defined->payment_detail_id,
                    is_form_wallet: 0,
                    currency: $transaction->tran_currency,
                    country_id: $payable->country_id,
                    status: 1
                );

            }
        } else {
            $this->updateDetailPayment($user_defined->payment_detail_id, 0, $session_response);
            $return_data['status'] = false;
            $return_data['message'] = $transaction->message ?? 'Payment failed';
        }
        return $return_data;
    }


    public function cancelSubscription($agreementId)
    {
        $client = new Client();
        $profileId = $this->data[KeysData::MerchantID];
        $response = $client->post(self::url . "/payment/agreement/cancel", [
            'headers' => [
                'Authorization' => "{$this->data[KeysData::ServerKey]}",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'profile_id' => $profileId,
                'agreement_id' => $agreementId,
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function refundSubscription($tran_ref, $order_id, $amount, $refund_reason)
    {

        Config::set('clickpay.profile_id', $this->data[KeysData::MerchantID]);
        Config::set('clickpay.server_key', $this->data[KeysData::ServerKey]);


        try {
            return paypage::refund($tran_ref, $order_id, $amount, $refund_reason);
        } catch (Exception $e) {
            return false;
        }

    }

    public function refund($tran_ref, $cart_id, $amount, $refund_reason, $cart_currency, $purchase_id)
    {
        $purchase = Purchase::find($purchase_id);
        Config::set('clickpay.profile_id', $this->data[KeysData::MerchantID]);
        Config::set('clickpay.server_key', $this->data[KeysData::ServerKey]);
        try {

            $client = new Client();
            $profileId = $this->data[KeysData::MerchantID];
            $response = $client->post(self::url . "payment/request", [
                'headers' => [
                    'Authorization' => "{$this->data[KeysData::ServerKey]}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'profile_id' => $profileId,
                    'tran_type' => "refund",
                    'tran_class' => 'ecom',

                    "cart_id" => $cart_id,
                    "cart_currency" => $cart_currency,
                    "cart_amount" => $amount??0,
                    "cart_description" => $refund_reason,
                    "tran_ref" => trim($tran_ref),
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), false);

            $success = ($result->payment_result->response_status == "A") ? true : false;
            return ['success' => $success, 'result' => $result];


        } catch (Exception $e) {
            dd($e->getMessage());
            return false;
        }
    }

    public function recurringPaySubscription($token, $tran_ref, $cart_id, $cart_currency, $cart_amount, $cart_description = "")
    {
        $client = new Client();
        $profileId = $this->data[KeysData::MerchantID];
        $response = $client->post(self::url . "/payment/request", [
            'headers' => [
                'Authorization' => "{$this->data[KeysData::ServerKey]}",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'profile_id' => $profileId,
                'tran_type' => "sale",
                'tran_class' => 'recurring',
                'token' => $token,
                'tran_ref' => $tran_ref,
                'cart_id' => $cart_id,
                'cart_currency' => $cart_currency,
                'cart_amount' => $cart_amount,
                'cart_description' => $cart_description

            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function renewSubscription($data)
    {
        $client = new Client();
        $response = $client->post("{self::url}/payment/request", [
            'headers' => [
                'Authorization' => "{$this->data[KeysData::ServerKey]}",
                'Content-Type' => 'application/json',
            ],
            'json' => $data,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }


    public function validate($data, $payment_detail_id)
    {

    }


}
