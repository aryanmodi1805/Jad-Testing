<?php

namespace App\Http\Middleware;

use App\Filament\Seller\Pages\ChargeCreditPage;
use App\Filament\Seller\Pages\SellerDashboard;
use App\Filament\Seller\Pages\StripeReturnPage;
use App\Filament\Seller\Pages\WalletPage;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictSellerWebPanel
{
    /**
     * Allowed route names for seller web panel (dashboard and wallet recharge only).
     */
    protected function allowedRouteNames(): array
    {
        return [
            SellerDashboard::getRouteName(),
            WalletPage::getRouteName(),
            ChargeCreditPage::getRouteName(),
            StripeReturnPage::getRouteName(),
        ];
    }

    /**
     * Allowed route name prefixes (e.g. auth, profile, otp).
     */
    protected function allowedRoutePrefixes(): array
    {
        return [
            'filament.seller.auth.',
            'filament.seller.pages.otp-page',
            'filament.seller.pages.my-profile',
            'filament.seller.pages.edit-profile',
            'filament.seller.pages.request-password-reset',
            'filament.seller.pages.reset-password',
        ];
    }

    /**
     * Allowed slug patterns (for tenant routes where name may vary).
     */
    protected function allowedSlugPatterns(): array
    {
        return ['dashboard', 'wallet-page', 'charge-credit-page', 'stripe-return-page'];
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();

        if (!$routeName || !str_starts_with($routeName, 'filament.seller.')) {
            return $next($request);
        }

        foreach ($this->allowedRouteNames() as $allowed) {
            if ($routeName === $allowed) {
                return $next($request);
            }
        }

        foreach ($this->allowedRoutePrefixes() as $prefix) {
            if (str_starts_with($routeName, $prefix) || $routeName === $prefix) {
                return $next($request);
            }
        }

        foreach ($this->allowedSlugPatterns() as $slug) {
            if (str_contains($routeName, $slug)) {
                return $next($request);
            }
        }

        return redirect()
            ->to(SellerDashboard::getUrl(tenant: getCurrentTenant()))
            ->with('please_use_app', true);
    }
}
