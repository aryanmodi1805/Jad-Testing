<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Bypass tenant verification for local development and testing
        if (app()->environment('local', 'testing')) {
            return $next($request);
        }

        if($request->path() == "livewire/update"){
            return $next($request);
        }

        $subdomain = getSubdomain();

        if ($subdomain == null) {
            return redirectToTenant(getTenant(), $request);
        }else{
            $tenant = getTenantBySubDomain($subdomain);

            if ($tenant == null) {
                return redirectToTenant(getTenant(), $request);
            }
        }

        return $next($request);
    }




}
