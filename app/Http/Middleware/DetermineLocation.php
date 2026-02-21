<?php

namespace App\Http\Middleware;

use App\Models\Country;
use Closure;
use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;
use Symfony\Component\HttpFoundation\Response;

class DetermineLocation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */

    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('userCountryCode')) {
            $ip = $request->ip();
            $location = Location::get($ip);

            $countryCode = $location ? $location->countryCode : 'SA';

            $country = Country::where('code', $countryCode)
                ->where('active', 1)
                ->first(['id', 'code']) ?? Country::where('code', 'SA')->first(['id', 'code']);

            $request->session()->put('userCountryCode', $country->code);
            $request->session()->put('userCountryId', $country->id);

        }
        return $next($request);
    }



}
