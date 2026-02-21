<?php

namespace App\Http\Controllers;

use App\Concerns\SubscribeFrom;
use App\Models\PaymentMethod;
use App\Models\Purchase;
use App\Models\Seller;
use App\Models\Subscription;
use App\Services\Payment\Tap;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Enums\RequestStatus;
use App\Enums\ResponseStatus;
use App\Events\RefreshResponseEvent;
use App\Events\RefreshRequestEvent;
use App\Models\Response;

class PaymentController extends Controller
{


    public function handleCallback(Request $request)
    {
        Log::channel('Tap')->info("TAP Callback request received at " . now()->toDateTimeString());
        Log::channel('Tap')->info("Callback payload: " . json_encode($request->all()));
        
        $content = $request->getContent();
        $paymentMethod = PaymentMethod::where('type', Tap::getProviderName())->firstOrFail();

        $response = (new Tap($paymentMethod->details))->successPayment($request->all(), $paymentMethod->id);

        // Store callback for debugging
        DB::table('webhook_calls')->insert([
            'name' => 'tap_callback',
            'url' => $request->url(),
            'payload' => json_encode($request->all()),
            'headers' => json_encode($request->headers->all()),
            'exception' => json_encode($response),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = auth('seller')->user() ?? $response['user'] ?? null;
        
        Log::channel('Tap')->info("Callback processing response: " . json_encode($response));
        
        if ($response && $response['status']) {
            // Handle service payment (70/30 split)
            if (isset($response['metadata']['action']) && $response['metadata']['action'] == 'service_payment') {
                 Log::channel('Tap')->info('Processing Service Payment - Redirecting to handler');
                 return self::handleServicePayment($response, $user);
            }

            // NEW: Handle API redirection for specific action
            if ((isset($response['action']) && $response['action'] == 'api') || (isset($response['metadata']['action']) && $response['metadata']['action'] == 'api')) {
                 Log::channel('Tap')->info('Redirecting to payment.success for API');
                 return redirect(route('payment.success'));
            }

            // Handle credit charge success
            if (empty($response['subscription_id']) && (!empty($response['required_credit']) || empty($response['product_id']))) {
                Log::channel('Tap')->info('Redirecting to wallet page - credit charge success');
                self::sendNotification(__('wallet.payment_status.success'), $user, __('wallet.added_to_balance', ['amount' => $response['required_credit']]));
                return redirect()->to('/seller/wallet-page?cr='.$response['required_credit'].'&e=0')->with('message', __('wallet.charge') . ':' . __('wallet.added_to_balance', ['amount' => $response['required_credit']]));
            }
            
            // Handle subscription success
            if (!empty($response['subscription_id'])) {
                Log::channel('Tap')->info('Redirecting to subscription page - subscription success');
                self::sendNotification($response['message'], $user, __('subscriptions.subscription_success'));
                return redirect()->to('/seller/subscription-plans')->with('message', __('subscriptions.subscription_success'));
            }
        }
        
        Log::channel('Tap')->info("Redirecting to appropriate page with error");

        // Attempt verification since payment failed/cancelled
        self::dispatchVerificationIfPending($response);

        if (isset($response['metadata']['action']) && $response['metadata']['action'] == 'service_payment') {
             Log::channel('Tap')->info("Service Payment Failed - Redirecting to failure page");
             return redirect(route('payment.failure'));
        }

        // NEW: Handle API failure redirection WITHOUT sending notification
        // API payments will get notification from IPN handler
        if ((isset($response['action']) && $response['action'] == 'api') || (isset($response['metadata']['action']) && $response['metadata']['action'] == 'api')) {
             Log::channel('Tap')->info("API Payment Failed - Redirecting to failure page (no notification sent, will be handled by IPN)");
             return redirect(route('payment.failure'));
        }

        // Only send failure notification for non-API payments (web interface)
        self::sendNotification($response['message'], $user, __('wallet.payment_status.failed'));
        return !empty($response['subscription_id'])
            ? redirect()->to('/seller/subscription-plans?s=1&e=1&status=' . urlencode($response['message']))->with('message', __('subscriptions.subscription_failed'))
            : redirect()->to('/seller/wallet-page?s=0&e=1&status=' . urlencode($response['message']))->with('message', __('wallet.failed_to_charge'));
    }

    public function handleIpn(Request $request)
    {
        Log::channel('Tap')->info("TAP IPN Request received at " . now()->toDateTimeString());
        Log::channel('Tap')->info("Request payload: " . json_encode($request->all()));
        Log::channel('Tap')->info("Request headers: " . json_encode($request->headers->all()));

        // Store webhook call for debugging
        DB::table('webhook_calls')->insert([
            'name' => 'tap_ipn',
            'url' => $request->url(),
            'payload' => json_encode($request->all()),
            'headers' => json_encode($request->headers->all()),
            'exception' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $paymentMethod = PaymentMethod::where('type', Tap::getProviderName())->firstOrFail();
            $requestData = $request->all();
            $response = (new Tap($paymentMethod->details))->successPayment($requestData, $paymentMethod->id);
            
            Log::channel('Tap')->info("Payment processing response: " . json_encode($response));
            
            $user = $response['user'] ?? null;
            $isPurchasedBefore = Purchase::where('transaction_id', $response['trans_ref'])->exists();
            
            if ($response && $response['status']) {
                // Handle service payment
                if (isset($response['metadata']['action']) && $response['metadata']['action'] == 'service_payment') {
                     Log::channel('Tap')->info("Processing IPN for Service Payment");
                     return self::handleServicePayment($response, $user);
                }

                // Handle credit charge (non-subscription payments)
                if (empty($response['subscription_id']) && (!empty($response['required_credit']) || empty($response['product_id'])) && !$isPurchasedBefore) {
                    Log::channel('Tap')->info("Processing credit charge");
                    return self::handleCreditCharge($response, $user, $requestData);
                }
                
                // Handle subscription payments
                if (!empty($response['subscription_id'])) {
                    Log::channel('Tap')->info("Processing subscription payment");
                    return self::handleSubscriptionSuccess($response, $user);
                }
            }

            Log::channel('Tap')->info("Payment failed or invalid");
            return self::handleFailure($response, $request, $user);

        } catch (\Exception $e) {
            Log::channel('Tap')->error("Error processing TAP IPN: " . $e->getMessage());
            Log::channel('Tap')->error("Stack trace: " . $e->getTraceAsString());
            
            // Update webhook call with exception
            DB::table('webhook_calls')
                ->where('name', 'tap_ipn')
                ->where('created_at', '>=', now()->subMinutes(5))
                ->update(['exception' => json_encode(['error' => $e->getMessage()])]);
                
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }


    public static function handleCreditCharge(array $response, $user, array $requestData)
    {
        $charged = chargeCreditBalance(
            payData: $response,
            gatewayModel: Tap::class,
            payable: $user,
            payment_details_id: $response['payment_details_id'],
            required_credit: $response['required_credit'],
            tran_currency: $response['tran_currency'] ?? $requestData['tran_currency']
        );
        if ($charged) {
            // Send success notification when credits are actually added
            self::sendNotification(__('wallet.payment_status.success'), $user, __('wallet.added_to_balance', ['amount' => $charged]));
            
            if (config('services.wafeq.enabled')) {
                Log::channel('Tap')->info("Wafeq invoice processing queued for wallet recharge: {$response['trans_ref']}");
            }
            
            return redirect(route('payment.success'));
        }

        // Send failure notification only if charge actually failed
        self::sendNotification(__('wallet.failed_to_charge'), $user, __('wallet.charge'));
        return redirect(route('payment.failure'));
    }

    public static function sendNotification(string $message, $user, string $title = '', $success = false): void
    {
        Notification::make()
            ->title($title)
            ->body($message)
            ->persistent()
            ->success()
            ->sendToDatabase($user)
            ->send();
    }

    public static function handleSubscriptionSuccess(array $response, $user)
    {
        try {
            // Additional security validation
            if (empty($response['subscription_id']) || empty($response['trans_ref'])) {
                Log::channel('Tap')->error('Invalid subscription response data', $response);
                return redirect(route('payment.failure'));
            }

            // Verify subscription exists and belongs to the user
            $subscription = Subscription::where('id', $response['subscription_id'])
                ->where('seller_id', $user?->id ?? 0)
                ->first();

            if (!$subscription) {
                Log::channel('Tap')->error('Subscription not found or unauthorized access', [
                    'subscription_id' => $response['subscription_id'],
                    'user_id' => $user?->id
                ]);
                return redirect(route('payment.failure'));
            }

            // Verify transaction hasn't been processed before
            $existingPurchase = Purchase::where('transaction_id', $response['trans_ref'])
                ->where('chargeable_type', Subscription::class)
                ->where('chargeable_id', $subscription->id)
                ->exists();

            if ($existingPurchase) {
                Log::channel('Tap')->warning('Duplicate subscription transaction detected', [
                    'transaction_id' => $response['trans_ref'],
                    'subscription_id' => $subscription->id
                ]);
                // Transaction already processed, redirect to success
                if ($response['action'] == 'api') {
                    return redirect(route('payment.success'));
                }
                self::sendNotification(__('subscriptions.subscription_success'), $user, __('subscriptions.subscription_success'));
                return redirect()->to('/seller/subscription-plans')->with('message', __('subscriptions.subscription_success'));
            }

            // Get payment method ID for the subscription
            $paymentMethod = PaymentMethod::where('type', Tap::getProviderName())->first();
            $paymentMethodId = $paymentMethod ? $paymentMethod->id : 0;

            // Update subscription status with additional validation
            $subscriptionUpdated = $subscription->update([
                'status' => 1,
                'payment_method_id' => $paymentMethodId,
                'payment_details' => $response,
                'payment_status' => 1,
                'trans_ref' => $response['trans_ref'],
                'token' => null, // No card saving for manual subscriptions
                'agreement_id' => null, // Manual subscriptions don't use agreements
                'subscribe_at' => now(),
                'updated_at' => now()
            ]);

            if (!$subscriptionUpdated) {
                Log::channel('Tap')->error('Failed to update subscription', [
                    'subscription_id' => $subscription->id,
                    'transaction_id' => $response['trans_ref']
                ]);
                return redirect(route('payment.failure'));
            }

            // Handle subscription renewal if this is a renewal action
            if (isset($response['action']) && $response['action'] == 'renew') {
                $subscription->renew();
                Log::channel('Tap')->info('Subscription renewed successfully', [
                    'subscription_id' => $subscription->id,
                    'transaction_id' => $response['trans_ref']
                ]);
            }

            // Create purchase record using SubscribeFrom
            $payable = $user ?? Seller::find($response['user_id'] ?? 0);
            $item = $subscription->plan;

            if ($payable && $item) {
                SubscribeFrom::createPurchase(
                    price: $response['amount_total'] ?? 0,
                    item: $item,
                    payment: Tap::getProviderName(),
                    payable: $payable,
                    transaction_id: $response['trans_ref'],
                    chargeable: $subscription,
                    payment_detail_id: $response['payment_details_id'] ?? null,
                    is_form_wallet: 0,
                    currency: $response['tran_currency'] ?? 'KWD',
                    country_id: $payable->country_id ?? null,
                    status: 1
                );

                Log::channel('Tap')->info('Purchase record created for subscription', [
                    'subscription_id' => $subscription->id,
                    'transaction_id' => $response['trans_ref'],
                    'user_id' => $payable->id,
                    'amount' => $response['amount_total'] ?? 0
                ]);
            }

            // Log successful subscription activation
            Log::channel('Tap')->info('Subscription successfully activated', [
                'subscription_id' => $subscription->id,
                'transaction_id' => $response['trans_ref'],
                'user_id' => $user?->id,
                'plan_id' => $subscription->price_plan_id,
                'amount' => $response['amount_total'] ?? 0
            ]);

            // Send notifications
            self::sendNotification(
                __('subscriptions.subscription_success'), 
                $user, 
                __('subscriptions.subscription_activated')
            );

            // Handle different response types
            if ($response['action'] == 'api') {
                // For API calls, redirect to success page that can be detected by mobile app
                return redirect(route('payment.success'));
            }

            // For web interface, redirect to subscription plans page
            return redirect()->to('/seller/subscription-plans')
                ->with('message', __('subscriptions.subscription_success'));

        } catch (\Exception $e) {
            Log::channel('Tap')->error('Error processing subscription success', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'response' => $response,
                'user_id' => $user?->id
            ]);
            
            // Send error notification to user
            if ($user) {
                self::sendNotification(
                    __('subscriptions.subscription_processing_error'), 
                    $user, 
                    __('wallet.payment_status.failed')
                );
            }

            return redirect(route('payment.failure'));
        }
    }

    private static function handleFailure(array $response, Request $request, $user)
    {
        try {
            // Attempt verification since payment failed/cancelled
            self::dispatchVerificationIfPending($response);

            // Log failure details for security monitoring
            Log::channel('Tap')->warning('Payment processing failed', [
                'response' => $response,
                'user_id' => $user?->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()
            ]);

            // If there's a subscription involved, mark it as failed
            if (!empty($response['subscription_id'])) {
                $subscription = Subscription::where('id', $response['subscription_id'])
                    ->where('seller_id', $user?->id ?? 0)
                    ->first();

                if ($subscription) {
                    $subscription->update([
                        'status' => 0, // Failed status
                        'payment_status' => 0,
                        'payment_details' => $response,
                        'updated_at' => now()
                    ]);

                    Log::channel('Tap')->info('Subscription marked as failed', [
                        'subscription_id' => $subscription->id,
                        'transaction_id' => $response['trans_ref'] ?? null
                    ]);
                }
            }

            // Send failure notification to user
            if ($user) {
                $title = !empty($response['subscription_id']) 
                    ? __('subscriptions.subscription_failed')
                    : __('wallet.payment_status.failed');
                    
                self::sendNotification(
                    $response['message'] ?? __('wallet.payment_processing_failed'), 
                    $user, 
                    $title
                );
            }

            // Handle different response types and redirect accordingly
            if ($response['action'] == 'api') {
                return redirect(route('payment.failure'));
            }
            
            if (isset($response['metadata']['action']) && $response['metadata']['action'] == 'service_payment') {
                 return redirect(route('payment.failure'));
            }

            // For web interface, redirect to appropriate page with error message
            if (!empty($response['subscription_id'])) {
                return redirect()->to('/seller/subscription-plans?s=1&e=1&status=' . urlencode($response['message'] ?? 'Payment failed'))
                    ->with('message', __('subscriptions.subscription_failed'));
            } else {
                return redirect()->to('/seller/wallet-page?s=0&e=1&status=' . urlencode($response['message'] ?? 'Payment failed'))
                    ->with('message', __('wallet.failed_to_charge'));
            }

        } catch (\Exception $e) {
            Log::channel('Tap')->error('Error handling payment failure', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'response' => $response,
                'user_id' => $user?->id
            ]);

            // Fallback to generic failure page
            return redirect(route('payment.failure'));
        }
    }
    public static function handleServicePayment(array $response, $user)
    {
        try {
            self::processServicePayment($response, $user);
            return redirect(route('payment.success'));
        } catch (\Exception $e) {
            Log::channel('Tap')->error("Service Payment Error: " . $e->getMessage());
            return redirect(route('payment.failure'));
        }
    }

    /**
     * Process service payment logic (throws exception on failure)
     */
    public static function processServicePayment(array $response, $user)
    {
        $responseId = $response['metadata']['response_id'] ?? null;
        if (!$responseId) {
            throw new \Exception('Service Payment: Missing response_id');
        }

        $sellerResponse = Response::find($responseId);
        if (!$sellerResponse) {
            throw new \Exception("Service Payment: Response not found $responseId");
        }

        // Avoid duplicate processing
        if ($sellerResponse->status == ResponseStatus::Hired && $sellerResponse->is_approved) {
             Log::channel('Tap')->info("Service Payment: Already processed for response $responseId");
             return; // Already done
        }

        $estimate = $sellerResponse->estimate;
        if (!$estimate) {
            throw new \Exception("Service Payment: Estimate not found for response $responseId");
        }

        $theRequest = $sellerResponse->request;
        if(!$theRequest) {
             throw new \Exception("Service Payment: Request not found for response $responseId");
        }

        // Calculate 70% earnings
        $earnings = $estimate->amount * 0.70;
        $seller = $sellerResponse->seller;

        // Deposit to Seller Wallet
        tx($earnings)
            ->processor('deposit')
            ->to($seller)
            ->overcharge()
            ->meta([
                'description' => "Online Payment: {$theRequest->service?->name} - {$theRequest->customer?->name} (70% of {$estimate->amount})",
                'response_id' => $responseId,
                'request_id' => $theRequest->id,
            ])
            ->commit();

        // Update Statuses
        $sellerResponse->status = ResponseStatus::Hired;
        $sellerResponse->is_approved = true;
        $sellerResponse->save();

        $theRequest->status = RequestStatus::Completed;
        $theRequest->save();

        // Reject other responses
        $theRequest->responses()->where('id', '!=', $sellerResponse->id)->update(['status' => ResponseStatus::Rejected]);

        // Broadcast Events
        $sellerIds = $theRequest->responses()->pluck('seller_id')->toArray();
        broadcast(new RefreshResponseEvent($sellerIds))->toOthers();

        Log::channel('Tap')->info("Service Payment: Successfully processed for response $responseId. Seller credited: $earnings");
        
        // Create Wafeq invoice ONLY after successful service payment processing
        if (config('services.wafeq.enabled')) {
            $wafeq = new \App\Services\Accounting\WafeqService();
            $result = $wafeq->createServicePaymentInvoice(
                customer: $theRequest->customer,
                seller: $seller,
                service: $theRequest->service?->name ?? 'Service',
                amount: $estimate->amount,
                transactionId: $response['trans_ref'],
                currency: 'SAR'
            );
            
            if ($result) {
                Log::channel('Tap')->info("Wafeq invoice processing successful for service payment: {$response['trans_ref']}");
            } else {
                Log::channel('Tap')->warning("Wafeq invoice processing failed for service payment: {$response['trans_ref']} (logged to failed_invoices)");
            }
        }
    }

    /**
     * Helper to dispatch verification job if a pending payment exists
     */
    private static function dispatchVerificationIfPending($response)
    {
        try {
            $chargeId = $response['id'] ?? $response['tap_id'] ?? null;
            if ($chargeId) {
                // Find pending payment
                $pendingPayment = \App\Models\PendingPayment::where('charge_id', $chargeId)->first();
                
                if ($pendingPayment) {
                    // Reset status if needed to allow the job to run fresh
                    if ($pendingPayment->status === 'expired' || $pendingPayment->status === 'failed') {
                        $pendingPayment->update([
                            'status' => 'pending',
                            'expires_at' => now()->addMinutes(5),
                            'verification_attempts' => 0
                        ]);
                    }
                    
                    // Dispatch verification job
                    \App\Jobs\VerifyPendingPayment::dispatch($chargeId);
                    Log::channel('Tap')->info("Auto-dispatched verification for charge: $chargeId");
                }
            }
        } catch (\Exception $e) {
            Log::channel('Tap')->error("Failed to auto-dispatch verification: " . $e->getMessage());
        }
    }

    /**
     * Verify payment status via Tap API
     * Called from frontend after payment redirect
     */
    public function verifyPayment(Request $request)
    {
        try {
            $tapId = $request->input('tap_id');
            
            if (!$tapId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing tap_id parameter'
                ], 400);
            }

            Log::channel('Tap')->info("Frontend payment verification request", [
                'tap_id' => $tapId,
                'user_id' => auth()->id()
            ]);

            // Get payment method for Tap
            $paymentMethod = PaymentMethod::where('type', Tap::getProviderName())->firstOrFail();
            
            // Verify charge with Tap API
            $tap = new Tap($paymentMethod->details);
            $verification = $tap->verifyCharge($tapId);

            if (!$verification['success']) {
                Log::channel('Tap')->error("Payment verification failed", [
                    'tap_id' => $tapId,
                    'error' => $verification['message'] ?? 'Unknown error'
                ]);

                return response()->json([
                    'success' => false,
                    'status' => 'error',
                    'message' => $verification['message'] ?? 'Failed to verify payment'
                ], 500);
            }

            $status = $verification['status'];
            $charge = $verification['charge'];

            Log::channel('Tap')->info("Payment verification result", [
                'tap_id' => $tapId,
                'status' => $status,
                'amount' => $charge['amount'] ?? null
            ]);

            // Return verification result to frontend
            return response()->json([
                'success' => true,
                'status' => $status,
                'is_captured' => $status === 'CAPTURED',
                'amount' => $charge['amount'] ?? 0,
                'currency' => $charge['currency'] ?? 'SAR',
                'message' => $charge['response']['message'] ?? 'Payment verified'
            ]);

        } catch (\Exception $e) {
            Log::channel('Tap')->error("Payment verification exception", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'An error occurred while verifying payment'
            ], 500);
        }
    }
}
