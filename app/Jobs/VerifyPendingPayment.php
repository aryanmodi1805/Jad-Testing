<?php

namespace App\Jobs;

use App\Models\PendingPayment;
use App\Models\PaymentMethod;
use App\Models\Seller;
use App\Services\Payment\Tap;
use App\Http\Controllers\PaymentController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class VerifyPendingPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $chargeId) {}

    public function handle(): void
    {
        Log::info("Starting VerifyPendingPayment for charge: {$this->chargeId}");
        
        $pendingPayment = PendingPayment::where('charge_id', $this->chargeId)
            ->whereIn('status', ['pending', 'verifying'])
            ->first();

        if (!$pendingPayment) {
            Log::channel('Tap')->info("No pending payment found for charge: {$this->chargeId}");
            return; // Already processed or doesn't exist
        }

        // Check if expired
        if ($pendingPayment->isExpired()) {
            $pendingPayment->update([
                'status' => 'expired',
                'failure_reason' => 'Payment session expired before capture.'
            ]);
            Log::channel('Tap')->info("Pending payment expired: {$this->chargeId}");
            return;
        }

        // Mark as verifying
        $pendingPayment->update(['status' => 'verifying']);

        // Verify with Tap
        $paymentMethod = PaymentMethod::where('type', Tap::getProviderName())->first();
        if (!$paymentMethod) {
            Log::channel('Tap')->error("Payment method not found for Tap");
            return;
        }

        $tap = new Tap($paymentMethod->details);
        $result = $tap->verifyCharge($this->chargeId);

        $pendingPayment->increment('verification_attempts');
        $pendingPayment->update(['last_verified_at' => now()]);

        Log::channel('Tap')->info("Verification attempt #{$pendingPayment->verification_attempts} for charge: {$this->chargeId}", [
            'status' => $result['status'] ?? 'unknown',
            'success' => $result['success'] ?? false
        ]);

        if ($result['success'] && $result['status'] === 'CAPTURED') {
            // Payment was successful!
            $charge = $result['charge'];
            $metadata = $charge['metadata'];
            
            Log::channel('Tap')->info("Payment CAPTURED - Processing payment for charge: {$this->chargeId}");
            
            // Build response array similar to successPayment
            $response = [
                'product_id' => $metadata['plan_id'] ?? null,
                'order_id' => $metadata['payment_detail_id'] ?? null,
                'amount_total' => $charge['amount'] ?? 0,
                'required_credit' => $metadata['required_credit'] ?? 0,
                'subscription_id' => $metadata['subscription_id'] ?? null,
                'user' => $pendingPayment->user,
                'status' => true,
                'message' => 'Payment successful',
                'payment_details_id' => $metadata['payment_detail_id'],
                'trans_ref' => $charge['id'] ?? null,
                'tran_currency' => $charge['currency'] ?? null,
                'action' => $metadata['action'] ?? null,
                'metadata' => $metadata,
            ];
            
            // Call the appropriate handler based on payment type
            // Call the appropriate handler based on payment type
            try {
                if ($pendingPayment->payment_type === 'service_payment') {
                    PaymentController::processServicePayment($response, $pendingPayment->user);
                } elseif ($pendingPayment->payment_type === 'credit_charge') {
                    PaymentController::handleCreditCharge($response, $pendingPayment->user, $charge);
                } elseif ($pendingPayment->payment_type === 'subscription') {
                    PaymentController::handleSubscriptionSuccess($response, $pendingPayment->user);
                }

                $pendingPayment->update(['status' => 'completed']);
                Log::channel('Tap')->info("Pending payment verified and completed: {$this->chargeId}");

            } catch (\Exception $e) {
                Log::channel('Tap')->error("Failed to process payment after verification: {$this->chargeId}", ['error' => $e->getMessage()]);
                // Keep status as verifying/pending to retry or let it expire if it's a persistent error
            }
            
        } elseif ($result['success'] && in_array($result['status'], ['FAILED', 'CANCELLED', 'DECLINED'])) {
            // Payment definitively failed
            $pendingPayment->update([
                'status' => 'failed',
                'failure_reason' => "Payment definitively failed with status: {$result['status']}"
            ]);
            Log::channel('Tap')->info("Payment definitively failed: {$this->chargeId} - Status: {$result['status']}");
            
        } else {
            // Still pending or unknown status, retry if not expired and under attempt limit
            if ($pendingPayment->verification_attempts < 10 && $pendingPayment->expires_at > now()->addSeconds(30)) {
                // Reset to pending for next attempt
                $pendingPayment->update(['status' => 'pending']);
                
                // Schedule next verification in 30 seconds
                VerifyPendingPayment::dispatch($this->chargeId)->delay(now()->addSeconds(30));
                
                Log::channel('Tap')->info("Scheduling next verification for charge: {$this->chargeId}");
            } else {
                // Max attempts reached or about to expire
                $pendingPayment->update([
                    'status' => 'expired',
                    'failure_reason' => "Max verification attempts reached ({$pendingPayment->verification_attempts})"
                ]);
                Log::channel('Tap')->warning("Pending payment expired after {$pendingPayment->verification_attempts} attempts: {$this->chargeId}");
            }
        }
    }
}
