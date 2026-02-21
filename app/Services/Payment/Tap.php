<?php


namespace App\Services\Payment;


use App\Models\Seller;
use App\Services\Payment\Concerns\KeysData;
use App\Services\Payment\Concerns\Payment;
use Clickpaysa\Laravel_package\Facades\paypage;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;


class Tap extends Payment
{


    const url = 'https://api.tap.company/v2/';

    public static function getKeysData(): array
    {
        return [
            KeysData::SecretKey => '',
            KeysData::SourceID => '',
            KeysData::MerchantID => '',
        ];
    }

    public static function getName(): string
    {
        return PaymentService::paymentsList[static::getProviderName()];
    }

    public static function getProviderName(): string
    {
        return 'tap';
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
        $amount = 0,
        $country_id = 0,
        $currency = 'SAR',
        $required_credit = 0,
        $subscription = null,
        $action = null
    )
    {
        // Check if amount is 0
        if ((float)$amount <= 0) {
            // Create payment detail with successful status
            $payment_detail_id = $this->newDetailPayment(
                $paymentMethodId,
                $product?->id ?? 0,
                $country_id,
                $customer->id,
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
                    'plan_id' => $product?->id ?? null,
                    'required_credit' => $required_credit,
                    'user_px' => $customer->id,
                    'action' => $action
                ])
            ];
        }

        // Original code for positive amounts
        $payment_detail_id = $this->newDetailPayment(
            $paymentMethodId,
            $product?->id ?? 0,
            $country_id,
            $customer->id,
            $amount);


        $customer_data = [
            'first_name' => trim($customer->name) ?? '',
            'email' => trim($customer->email) ?? "",
            'phone' => [
                'country_code' => $customer->country?->phonecode ?? '966',
                'number' => trim($customer->phone) ?? '',
            ]
        ];

        // This metadata will be sent to Tap and returned to us in the callback.
        $metadata = [
            "payment_detail_id" => $payment_detail_id,
            "subscription_id" => $subscription?->id ?? null,
            "plan_id" => $product?->id ?? null,
            "required_credit" => $required_credit ?? "0",
            "user_px" => $customer->id ?? null,
            "action" => $action ?? null,
        ];

        // Determine redirect URL based on action
        // For API calls (mobile app), use simple success/failure URLs that the WebView can detect
        // For web/Filament calls, use the callback route
        $redirectUrl = ($action === 'api_charge') 
            ? url('api/wallet/payment-callback') // Redirect to callback handler
            : route('tap.callback');    // Web/Filament uses authenticated callback

        $payload = [
            'amount' => (float)$amount,
            'currency' => Str::upper($currency),
            'threeDSecure' => true,
            'save_card' => false, // Manual subscription management - no card saving for now
            'customer_initiated' => true,
            'description' => ($product ? ($product->getWalletMeta()['data'] ?? null) : null) ?? __('wallet.buy_credit') . ' (' . $required_credit . ')',
            'statement_descriptor' => 'JAD',
            'metadata' => $metadata,
            'customer' => $customer_data,
            'source' => ['id' => $this->data[KeysData::SourceID]], // This tells Tap what payment methods to show
            'post' => ['url' => route('tap.ipn')], // Server-to-server callback URL
            'redirect' => ['url' => $redirectUrl], // URL user is redirected to after payment
        ];

        // For subscriptions, add receipt configuration
        if ($subscription) {
            $payload['receipt'] = [
                'email' => true,
                'sms' => false
            ];
        }

        try {

            $response = Http::withToken($this->data[KeysData::SecretKey])
                ->post(self::url . 'charges', $payload);


            if ($response->successful()) {
                $charge = $response->json();
                
                // Store as pending payment for verification (for credit charges)
                if (!empty($required_credit) && empty($subscription)) {
                    \App\Models\PendingPayment::create([
                        'user_id' => $customer->id,
                        'user_type' => 'seller', // Credit charges are made by sellers
                        'charge_id' => $charge['id'],
                        'response_id' => null, // No response for credit charges
                        'payment_type' => 'credit_charge',
                        'amount' => $amount,
                        'currency' => $currency,
                        'status' => 'pending',
                        'expires_at' => now()->addMinutes(5),
                        'metadata' => $metadata
                    ]);
                    
                    // Dispatch verification job
                    \App\Jobs\VerifyPendingPayment::dispatch($charge['id'])->delay(now()->addSeconds(30));
                    
                    Log::info('Pending credit charge created and verification job dispatched', [
                        'charge_id' => $charge['id'],
                        'user_id' => $customer->id,
                        'amount' => $amount
                    ]);
                }
                
                // We need to redirect the user to the URL provided by Tap.
                return redirect($charge['transaction']['url']);
            } else {
                // Handle API errors
                $error = $response->json('errors.0.description', 'An unknown error occurred with Tap.');
                Notification::make()->body($error)->danger()->send();
                $this->updateDetailPayment($payment_detail_id, 0, $response->json());
                return null;
            }
        } catch (Exception $e) {
            Notification::make()->body($e->getMessage())->danger()->send();
            $this->updateDetailPayment($payment_detail_id, 0, ['error' => $e->getMessage()]);
        }


        return null;
    }

    /**
     * Verify a charge status with Tap API
     * Used for background verification of pending payments
     */
    public function verifyCharge($chargeId)
    {
        try {
            $response = Http::withToken($this->data[KeysData::SecretKey])
                ->get(self::url . 'charges/' . $chargeId);

            if (!$response->successful()) {
                return [
                    'success' => false, 
                    'message' => 'Failed to verify charge with Tap',
                    'status' => 'unknown'
                ];
            }

            $charge = $response->json();
            
            return [
                'success' => true,
                'status' => $charge['status'] ?? 'unknown',
                'charge' => $charge,
                'metadata' => $charge['metadata'] ?? []
            ];
        } catch (Exception $e) {
            return [
                'success' => false, 
                'message' => $e->getMessage(),
                'status' => 'error'
            ];
        }
    }



    public function successPayment($request_data, $paymentMethodId = 0)
    {

        $chargeId = $request_data['tap_id'] ?? $request_data['id'] ?? null;

        try {
            // Verify the transaction by retrieving the charge from Tap's API
            $response = Http::withToken($this->data[KeysData::SecretKey])
                ->get(self::url . 'charges/' . $chargeId);

            if (!$response->successful()) {
                return ['status' => false, 'message' => 'Failed to verify transaction with Tap.'];
            }

            $charge = $response->json();
            $metadata = $charge['metadata'];

            $return_data = [
                'product_id' => $metadata['plan_id'] ?? null,
                'order_id' => $metadata['payment_detail_id'] ?? null,
                'amount_total' => $charge['amount'] ?? 0,
                'required_credit' => $metadata['required_credit'] ?? 0,
                'subscription_id' => $metadata['subscription_id'] ?? null,
                'user' => auth('seller')->user() ?? Seller::find($metadata['user_px']) ?? null,
                'status' => false, // Default to false
                'message' => $charge['response']['message'] ?? 'Payment verification failed.',
                'payment_details_id' => $metadata['payment_detail_id'],
                'trans_ref' => $charge['id'] ?? null,
                'tran_currency' => $charge['currency'] ?? null,
                'action' => $metadata['action'] ?? null,
                'metadata' => $metadata, // Preserve full metadata for callback handling
            ];

            // Check if the charge status is 'CAPTURED' (successful)
            if (isset($charge['status']) && $charge['status'] === 'CAPTURED') {
                $this->updateDetailPayment($metadata['payment_detail_id'], 1, $charge);
                
                // Update Pending Payment status to completed
                if ($chargeId) {
                    \App\Models\PendingPayment::where('charge_id', $chargeId)
                        ->update(['status' => 'completed', 'last_verified_at' => now()]);
                }
                
                $return_data['status'] = true;
                $return_data['message'] = 'Payment successful';
            } else {
                // If payment was not captured (e.g., failed, abandoned)
                $this->updateDetailPayment($metadata['payment_detail_id'], 0, $charge);
                
                // Update Pending Payment status to failed
                if ($chargeId) {
                    \App\Models\PendingPayment::where('charge_id', $chargeId)
                        ->update(['status' => 'failed', 'last_verified_at' => now()]);
                }
                
                $return_data['message'] = $charge['response']['message'] ?? 'Payment failed';
            }

            return $return_data;

        } catch (Exception $e) {
            Notification::make()->body($e->getMessage())->danger()->send();
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function refund($tran_ref, $cart_id, $amount, $refund_reason, $cart_currency, $purchase_id)
    {
        Log::channel('Tap')->info('refunding');
        $payload = [
            'charge_id' => $tran_ref,
            'amount' => $amount,
            'currency' => $cart_currency,
            'reason' => $refund_reason,
            'metadata' => [
                'purchase_id' => $purchase_id,
                'cart_id' => $cart_id,
            ]
        ];

        Log::channel('Tap')->info(json_encode($payload));


        try {
            Log::channel('Tap')->info('refunding request');
            $response = Http::withToken($this->data[KeysData::SecretKey])
                ->post(self::url . 'refunds', $payload);
            Log::channel('Tap')->info(json_encode($response->json()));

            if ($response->successful() && $response->json('status') === 'REFUNDED') {
                $refund = $response->json();
                return [
                    'success' => true,
                    'result' => [
                        'tran_ref' => $refund['id'],
                        'payment_result' => [
                            'response_message' => $refund['response']['message'],
                        ]
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'result' => [
                        'tran_ref' => null,
                        'payment_result' => [
                            'response_message' => 'failed',
                        ]
                    ]
                ];
            }
        } catch (Exception $e) {
            Notification::make()->body($e->getMessage())->danger()->send();
            Log::channel('Tap')->error($e->getMessage());
            return ['success' => false, 'result' => ['error' => $e->getMessage()]];
        }
    }

    /**
     * Create a manual renewal payment for a subscription
     * This method creates a new payment for subscription renewal
     */
    public function createSubscriptionRenewal(
        $paymentMethodId,
        $subscription,
        $amount,
        $currency = 'SAR'
    )
    {
        $customer = $subscription->seller;
        $plan = $subscription->plan;

        $payment_detail_id = $this->newDetailPayment(
            $paymentMethodId,
            $plan->id,
            $customer->country_id,
            $customer->id,
            $amount
        );

        $customer_data = [
            'first_name' => trim($customer->name) ?? '',
            'email' => trim($customer->email) ?? "",
            'phone' => [
                'country_code' => $customer->country?->phonecode ?? '966',
                'number' => trim($customer->phone) ?? '',
            ]
        ];

        $metadata = [
            "payment_detail_id" => $payment_detail_id,
            "subscription_id" => $subscription->id,
            "plan_id" => $plan->id,
            "user_px" => $customer->id,
            "action" => "renew",
        ];

        $payload = [
            'amount' => (float)$amount,
            'currency' => Str::upper($currency),
            'threeDSecure' => true,
            'save_card' => false, // Manual renewals don't save cards
            'customer_initiated' => false, // This is a merchant-initiated transaction
            'description' => __('subscriptions.renewal_payment') . ' - ' . $plan->name,
            'statement_descriptor' => 'JAD Renewal',
            'metadata' => $metadata,
            'customer' => $customer_data,
            'source' => ['id' => $this->data[KeysData::SourceID]],
            'post' => ['url' => route('tap.ipn')],
            'redirect' => ['url' => route('tap.callback')],
            'receipt' => [
                'email' => true,
                'sms' => false
            ]
        ];

        try {
            $response = Http::withToken($this->data[KeysData::SecretKey])
                ->post(self::url . 'charges', $payload);

            if ($response->successful()) {
                $charge = $response->json();
                return ['success' => true, 'payment_url' => $charge['transaction']['url']];
            } else {
                $error = $response->json('errors.0.description', 'An unknown error occurred with Tap.');
                $this->updateDetailPayment($payment_detail_id, 0, $response->json());
                return ['success' => false, 'error' => $error];
            }
        } catch (Exception $e) {
            $this->updateDetailPayment($payment_detail_id, 0, ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create a payment for a service request (response)
     */
    public function createServicePayment(
        $paymentMethodId,
        $customer,
        $amount,
        $currency = 'SAR', // Default currency
        $responseModel
    )
    {
        $plan_id = 0; // No plan for service payment
        
        $payment_detail_id = $this->newDetailPayment(
            $paymentMethodId,
            $plan_id, 
            $customer->country_id ?? 0,
            $customer->id,
            $amount
        );

        $customer_data = [
            'first_name' => trim($customer->name) ?? '',
            'email' => trim($customer->email) ?? "",
            'phone' => [
                'country_code' => $customer->country?->phonecode ?? '966',
                'number' => trim($customer->phone) ?? '',
            ]
        ];

        $metadata = [
            "payment_detail_id" => $payment_detail_id,
            "response_id" => $responseModel->id,
            "user_px" => $customer->id,
            "action" => "service_payment", // Custom action for service payments
        ];

        $payload = [
            'amount' => (float)$amount,
            'currency' => Str::upper($currency),
            'threeDSecure' => true,
            'save_card' => false,
            'customer_initiated' => true,
            'description' => 'Service Payment: ' . ($responseModel->request->service?->name ?? 'Service'),
            'statement_descriptor' => 'JAD Service',
            'metadata' => $metadata,
            'customer' => $customer_data,
            'source' => ['id' => $this->data[KeysData::SourceID]],
            'post' => ['url' => route('tap.ipn')],
            'redirect' => ['url' => route('tap.callback')],
        ];

        Log::info('Tap Service Payment Payload:', $payload);

        try {
            $apiResponse = Http::withToken($this->data[KeysData::SecretKey])
                ->post(self::url . 'charges', $payload);

            if ($apiResponse->successful()) {
                $charge = $apiResponse->json();
                
                // Store as pending payment for verification
                \App\Models\PendingPayment::create([
                    'user_id' => $customer->id,
                    'user_type' => 'customer', // Service payments are made by customers
                    'charge_id' => $charge['id'],
                    'response_id' => $responseModel->id,
                    'payment_type' => 'service_payment',
                    'amount' => $amount,
                    'currency' => $currency,
                    'status' => 'pending',
                    'expires_at' => now()->addMinutes(5),
                    'metadata' => $metadata
                ]);
                
                // Dispatch verification job to start checking after 30 seconds
                \App\Jobs\VerifyPendingPayment::dispatch($charge['id'])->delay(now()->addSeconds(30));
                
                Log::info('Pending payment created and verification job dispatched', [
                    'charge_id' => $charge['id'],
                    'customer_id' => $customer->id
                ]);
                
                return $charge['transaction']['url'];
            } else {
                $error = $apiResponse->json('errors.0.description', 'Tap Error');
                $this->updateDetailPayment($payment_detail_id, 0, $apiResponse->json());
                Log::error('Tap Service Payment Error: ' . $error);
                return null;
            }
        } catch (Exception $e) {
            $this->updateDetailPayment($payment_detail_id, 0, ['error' => $e->getMessage()]);
            Log::error('Tap Service Payment Exception: ' . $e->getMessage());
            return null;
        }
    }
}

