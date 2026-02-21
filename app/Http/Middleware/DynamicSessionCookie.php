<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DynamicSessionCookie
{
    /**
     * Handle an incoming request.
     *
     * IMPORTANT:
     * This middleware intentionally does NOTHING.
     *
     * Laravel + Livewire + Filament require a single,
     * stable session cookie name. Dynamically changing
     * the session cookie causes Livewire snapshot
     * corruption and fatal errors.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
