<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AppleDeviceBalanceCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Check if the request is from an iOS device and user is authenticated
        $userAgent = $request->header('User-Agent', '');
        $isIOS = str_contains(strtolower($userAgent), 'ios') ||
                 str_contains(strtolower($userAgent), 'iphone') ||
                 str_contains(strtolower($userAgent), 'ipad');

        // Only process for authenticated users on iOS devices
        if ($isIOS && auth()->check()) {
            $user = auth()->user();

            // Check if user is a seller (customers don't need credits)
            if ($user instanceof \App\Models\Seller) {
                try {
                    // Get user balance using balanceFloatNum property (returns float)
                    $balance = $user->balance();

                    // Check for active subscriptions
                    $hasActiveSubscription = $user->subscriptions()
                        ->active()
                        ->exists();

                    // Add headers for balance and subscription status
                    $response->headers->set('X-User-Balance', (string) $balance);
                    $response->headers->set('X-Has-Active-Subscription', $hasActiveSubscription ? '1' : '0');

                    // Add specific header if user has 0 credits and no subscription
                    if ($balance->value->lessThan(1) && ! $hasActiveSubscription) {
                        $response->headers->set('X-Show-Charge-Dialog', '1');
                    } else {
                        $response->headers->set('X-Show-Charge-Dialog', '0');
                    }
                } catch (\Exception $e) {
                    // Log error and set safe defaults
                    \Log::error('AppleDeviceBalanceCheckMiddleware error: '.$e->getMessage(), [
                        'user_id' => $user->id,
                        'balance' => $user->balance()->value->equals(0),
                        'exception' => $e,
                    ]);

                    // Set safe default headers
                    $response->headers->set('X-User-Balance', '0');
                    $response->headers->set('X-Has-Active-Subscription', '0');
                    $response->headers->set('X-Show-Charge-Dialog', '1');
                }
            }
        }

        return $response;
    }
}
