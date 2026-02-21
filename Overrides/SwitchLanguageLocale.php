<?php

namespace BezhanSalleh\FilamentLanguageSwitch\Http\Middleware;

use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Closure;
use Illuminate\Http\Request;

class SwitchLanguageLocale
{
    public function handle(Request $request, Closure $next): mixed
    {
        app()->setLocale(
            locale: LanguageSwitch::make()->getPreferredLocale()
        );
        config(['filament-google-maps.locale.api' => strtoupper(LanguageSwitch::make()->getPreferredLocale())]);
        return $next($request);
    }
}
