<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBlocked
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->blocked) {
            // Revoke current token if using Sanctum
            if ($user->currentAccessToken()) {
                $user->currentAccessToken()->delete();
            }

            // Also try to logout from web guards just in case
            if (auth('seller')->check()) auth('seller')->logout();
            if (auth('customer')->check()) auth('customer')->logout();

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => \Lang::get('api.account_blocked'),
                    'code' => 403
                ], 403);
            }

            return redirect()->route('blocked');
        }

        return $next($request);
    }
}
