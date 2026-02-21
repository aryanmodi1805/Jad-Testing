<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PendingPayment;
use App\Jobs\VerifyPendingPayment;
use Illuminate\Http\Request;

class PendingPaymentController extends Controller
{
    /**
     * Check if user has a pending payment
     */
    public function checkPending(Request $request)
    {
        $user = auth('seller')->user() ?? auth('customer')->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        
        $pendingPayment = PendingPayment::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'verifying'])
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
        
        if ($pendingPayment) {
            return response()->json([
                'has_pending' => true,
                'charge_id' => $pendingPayment->charge_id,
                'payment_type' => $pendingPayment->payment_type,
                'amount' => $pendingPayment->amount,
                'currency' => $pendingPayment->currency,
                'status' => $pendingPayment->status,
                'expires_at' => $pendingPayment->expires_at->toIso8601String(),
                'seconds_remaining' => $pendingPayment->secondsRemaining(),
                'verification_attempts' => $pendingPayment->verification_attempts,
                'last_verified_at' => $pendingPayment->last_verified_at?->toIso8601String(),
            ]);
        }
        
        return response()->json(['has_pending' => false]);
    }
    
    /**
     * Manually trigger verification for a pending payment
     */
    public function manualVerify(Request $request, $chargeId)
    {
        $user = auth('seller')->user() ?? auth('customer')->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        
        $pendingPayment = PendingPayment::where('user_id', $user->id)
            ->where('charge_id', $chargeId)
            ->whereIn('status', ['pending', 'verifying'])
            ->first();
        
        if (!$pendingPayment) {
            return response()->json(['error' => 'No pending payment found'], 404);
        }
        
        // Trigger immediate verification
        VerifyPendingPayment::dispatch($chargeId);
        
        return response()->json([
            'message' => 'Verification triggered',
            'charge_id' => $chargeId
        ]);
    }
    
    /**
     * Get status of a specific pending payment
     */
    public function getStatus(Request $request, $chargeId)
    {
        $user = auth('seller')->user() ?? auth('customer')->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        
        $pendingPayment = PendingPayment::where('user_id', $user->id)
            ->where('charge_id', $chargeId)
            ->first();
        
        if (!$pendingPayment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }
        
        return response()->json([
            'charge_id' => $pendingPayment->charge_id,
            'payment_type' => $pendingPayment->payment_type,
            'amount' => $pendingPayment->amount,
            'currency' => $pendingPayment->currency,
            'status' => $pendingPayment->status,
            'is_active' => $pendingPayment->isActive(),
            'is_expired' => $pendingPayment->isExpired(),
            'expires_at' => $pendingPayment->expires_at->toIso8601String(),
            'seconds_remaining' => $pendingPayment->secondsRemaining(),
            'verification_attempts' => $pendingPayment->verification_attempts,
            'last_verified_at' => $pendingPayment->last_verified_at?->toIso8601String(),
            'created_at' => $pendingPayment->created_at->toIso8601String(),
        ]);
    }
}
