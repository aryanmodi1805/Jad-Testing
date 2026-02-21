<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;
use function PHPUnit\Framework\stringContains;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasHeader('Accept-Language')) {
            $locale = substr($request->header('Accept-Language'), 0, 10); // Limit length for security
            
            // Handle complex headers like "en-US,en;q=0.9"
            // 1. Get first language preference
            $locale = explode(',', $locale)[0];
            // 2. Remove quality values (;)
            $locale = explode(';', $locale)[0];
            // 3. Normalize separators and get primary language code
            $locale = str_replace('_', '-', $locale);
            $locale = explode('-', $locale)[0];

            if (!in_array($locale, ['ar', 'en'])) {
                $locale = 'ar';
            }

            App::setLocale($locale);
        } else {
            App::setLocale("ar");
        }
        return $next($request);
    }
}
