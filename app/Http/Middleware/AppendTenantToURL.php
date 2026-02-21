<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AppendTenantToURL
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->attributes->has("country")) {
            // Country code is already present in the URL, proceed without modification
            return $next($request);
        } else {
            // Determine the user's country code (e.g., 'YE' for Yemen)
            $countryCode = getCountryCode();

            // Append the country code to the request if it's not already present
            if ($countryCode) {
                $request->attributes->add(['country' => $countryCode]);
            }
        }

        return $next($request);
    }
}
