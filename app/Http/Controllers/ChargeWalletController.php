<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class ChargeWalletController extends Controller
{
    /**
     * Predefined wallet charge amounts (in SAR)
     */
    private const PREDEFINED_AMOUNTS = [1, 50, 100, 150, 200, 300, 400];

    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'web'])->except(['handlePaymentCallback']);
    }

    /**
     * Get predefined wallet charge amounts with VAT calculations
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPredefinedAmounts()
    {
        try {
            $tenant = getCurrentTenant();
            $vatPercentage = 0; // VAT disabled
            $currency = $tenant?->currency?->symbol ?? 'SAR';
            $creditPrice = $tenant?->credit_price ?? 1;

            // If credit_price is 0, use 1 as default
            if ($creditPrice <= 0) {
                $creditPrice = 1;
            }

            $amounts = collect(self::PREDEFINED_AMOUNTS)->map(function ($credits) use ($creditPrice, $vatPercentage, $currency) {
                $baseAmount = $credits * $creditPrice;
                $vatAmount = 0;
                $totalAmount = $baseAmount; 

                return [
                    'credits' => $credits,
                    'base_amount' => $baseAmount,
                    'vat_percentage' => 0,
                    'vat_amount' => 0,
                    'total_amount' => $totalAmount,
                    'currency' => $currency,
                    'currency_code' => $tenant?->currency?->code ?? 'SAR',
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'amounts' => $amounts,
                    'credit_price' => $creditPrice,
                    'vat_percentage' => 0,
                    'currency' => $currency,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching predefined amounts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch predefined amounts'
            ], 500);
        }
    }

    /**
     * Initiate wallet charge payment for selected amount
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function initiatePayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'credits' => 'required|integer|in:' . implode(',', self::PREDEFINED_AMOUNTS),
                'payment_method_id' => 'required|integer|exists:payment_methods,id',
            ]);

            $user = $request->user();
            $credits = $validated['credits'];
            $paymentMethodId = $validated['payment_method_id'];

            // Get payment method
            $paymentMethod = PaymentMethod::findOrFail($paymentMethodId);
            
            if (!$paymentMethod->active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected payment method is not active'
                ], 400);
            }

            // Calculate amounts
            $tenant = getCurrentTenant();
            $creditPrice = $tenant?->credit_price ?? 1;
            if ($creditPrice <= 0) {
                $creditPrice = 1;
            }

            $baseAmount = $credits * $creditPrice;
            $totalAmount = $baseAmount; // No VAT

            // Check for existing pending payment
            $pendingPayment = \App\Models\PendingPayment::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'verifying'])
                ->where('payment_type', 'credit_charge')
                ->where('expires_at', '>', now())
                ->first();

            if ($pendingPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have a pending payment. Please wait ' . $pendingPayment->secondsRemaining() . ' seconds for verification.',
                    'pending' => true,
                    'seconds_remaining' => $pendingPayment->secondsRemaining()
                ], 409);
            }

            // Get payment gateway
            $paymentClass = PaymentService::getPayment($paymentMethod->type);
            $gateway = new $paymentClass($paymentMethod->details);

            // Create payment
            $result = $gateway->createPayment(
                paymentMethodId: $paymentMethod->id,
                customer: $user,
                product: null,
                amount: $totalAmount,
                country_id: getCountryId(),
                currency: $tenant?->currency?->code ?? 'SAR',
                required_credit: $credits,
                action: 'api_charge'
            );

            // If result is a redirect response, extract the URL
            if ($result instanceof \Illuminate\Http\RedirectResponse) {
                $paymentUrl = $result->getTargetUrl();
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'payment_url' => $paymentUrl,
                        'credits' => $credits,
                        'total_amount' => $totalAmount,
                    ]
                ]);
            }

            // Handle free payment case
            if (is_array($result) && isset($result['success']) && $result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'payment_url' => null,
                        'credits' => $credits,
                        'total_amount' => 0,
                        'free_payment' => true
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment'
            ], 500);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error initiating wallet charge payment: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available payment methods
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentMethods()
    {
        try {
            $paymentMethods = PaymentMethod::select('id', 'name', 'logo', 'type')
                ->where('active', 1)
                ->get()
                ->map(function ($method) {
                    return [
                        'id' => $method->id,
                        'name' => $method->name,
                        'logo' => $method->logo,
                        'type' => $method->type,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $paymentMethods
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching payment methods: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment methods'
            ], 500);
        }
    }


    /**
     * Handle payment callback from Tap
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    /**
     * Handle payment callback from Tap
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handlePaymentCallback(Request $request)
    {
        $tapId = $request->input('tap_id');
        
        if (!$tapId) {
            return redirect('/payment/failure?message=' . urlencode('Missing payment reference'));
        }

        try {
            return DB::transaction(function () use ($tapId, $request) {
                // 1. Lock the pending payment record to prevent race conditions
                $pending = \App\Models\PendingPayment::where('charge_id', $tapId)
                    ->lockForUpdate()
                    ->first();

                // 2. Validate Ownership & Expiry
                if (!$pending) {
                    return redirect('/payment/failure?message=' . urlencode('Invalid payment reference'));
                }
                
                // 3. Idempotency Check (If already completed, just show success)
                if ($pending->status === 'completed') {
                    return redirect('/payment/success');
                }
                
                if ($pending->isExpired()) {
                     $pending->update(['status' => 'failed']);
                     return redirect('/payment/failure?message=' . urlencode('Payment expired'));
                }

                // 4. Verify with Tap API
                $paymentMethod = PaymentMethod::where('type', 'tap')->first();
                if (!$paymentMethod) {
                     $paymentMethod = PaymentMethod::where('type', 'Tap')->firstOrFail();
                }
                
                // Instantiate Tap service
                $tap = new \App\Services\Payment\Tap($paymentMethod->details);
                $verification = $tap->verifyCharge($tapId);

                if (!$verification['success']) {
                    $message = $verification['message'] ?? 'Payment verification failed';
                     $pending->update(['status' => 'failed']);
                    return redirect('/payment/failure?message=' . urlencode($message));
                }

                $status = $verification['status'];
                
                    // 5. Grant Credits ONLY if status is CAPTURED/COMPLETED
                if ($status === 'CAPTURED' || $status === 'COMPLETED' || $status === 'authorized') {
                    
                    // Mark as completed FIRST to prevent re-entry
                    $pending->update(['status' => 'completed']);

                    // Retrieve credits from metadata with fallback
                    $metadata = $pending->metadata ?? [];
                    $credits = $metadata['credits'] ?? $metadata['required_credit'] ?? 0;

                    if ($credits <= 0) {
                         Log::error("Validation Error: Zero credits found for payment {$tapId}");
                         // Even if 0 credits, we mark as completed so we don't retry. User can contact support.
                         return redirect('/payment/failure?message=' . urlencode('System Error: Invalid credit amount'));
                    }
                    
                    // Call chargeCreditBalance helper
                    // function chargeCreditBalance($payData, $gatewayModel, $payable, $divider = 1, $payment_details_id = null, $required_credit = null, $tran_currency = null)
                    
                    try {
                        chargeCreditBalance(
                            payData: [
                                'product_id' => null,
                                'required_credit' => $credits,
                                'amount_total' => $pending->amount, // Total price paid
                                'trans_ref' => $tapId,
                                'payment_details_id' => $tapId, // Use tapId as reference
                            ],
                            gatewayModel: \App\Services\Payment\Tap::class,
                            payable: $pending->user,
                            payment_details_id: $tapId,
                            required_credit: $credits,
                            tran_currency: $pending->currency
                        );
                        
                        if (config('services.wafeq.enabled')) {
                            Log::info("Wafeq invoice processing queued for wallet recharge: {$tapId}");
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to credit wallet: ' . $e->getMessage());
                        // Maintain status completed but log error? Or fail?
                        // If we fail here, user paid but didn't get credits. 
                        // Better to keep it completed and let admin resolve, or revert?
                        // DB transaction will revert everything if we throw.
                        throw $e;
                    }

                    return redirect('/payment/success');
                } else {
                     // Payment failed/cancelled
                     $message = $verification['charge']['response']['message'] ?? 'Payment ' . strtolower($status);
                     $pending->update(['status' => 'failed']);
                     return redirect('/payment/failure?message=' . urlencode($message));
                }
            });

        } catch (\Exception $e) {
            Log::error('Error handling payment callback: ' . $e->getMessage());
            return redirect('/payment/failure?message=' . urlencode('System error during payment verification: ' . $e->getMessage()));
        }
    }
}
