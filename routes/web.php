<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;
use BezhanSalleh\FilamentLanguageSwitch\Events\LocaleChanged;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// ✅ CRITICAL FIX: Register Livewire asset routes FIRST to prevent catch-all interception
Livewire::setUpdateRoute(function ($handle) {
    return Route::post('/livewire/update', $handle)->middleware('web');
});

Livewire::setScriptRoute(function ($handle) {
    return Route::get('/livewire/livewire.js', $handle);
});


Route::post('/broadcasting/auth', function () {
    return Filament::auth()->user();
});
Route::post('/seller/stripe-return-page', [\App\Filament\Seller\Pages\StripeReturnPage::class,'updateCartByIPN'])->name('stripe-return-page');
// This is the URL the user is sent back to after payment.
Route::get('/seller/tap-callback', [PaymentController::class, 'handleCallback'])->name('tap.callback');

// This is the URL Tap sends server-to-server notifications to.
Route::post('/seller/tap-ipn', [PaymentController::class, 'handleIpn'])->name('tap.ipn');



Route::get('/payment/success', function () {
    return 'Payment Success';
})->name('payment.success');
Route::get('/payment/failure', function () {
    return 'Payment failure';
})->name('payment.failure');

Route::controller(AuthController::class)->group(function () {
    Route::post('/customer/token', 'customerToken');
    Route::post('/seller/token', 'sellerToken');
    Route::post('/admin/token', 'adminToken');
});

Route::get('/seller/charge', function (Request $request){
    $subdomain = getSubdomain();

    if ($subdomain == null) {
        return redirectToTenant(getTenant(), $request);
    }else{
        $tenant = getTenantBySubDomain($subdomain);

        if ($tenant == null) {
            return redirectToTenant(getTenant(), $request);
        }
    }
   return redirect()->route('filament.seller.pages.wallet-page', ['tenant' => $subdomain]);
});


Route::get('/seller/subscribe', function (Request $request){

    $subdomain = getSubdomain();

    if ($subdomain == null) {
        return redirectToTenant(getTenant(), $request);
    }else{
        $tenant = getTenantBySubDomain($subdomain);

        if ($tenant == null) {
            return redirectToTenant(getTenant(), $request);
        }
    }
    return redirect()->route('filament.seller.pages.subscription-plans', ['tenant' => $subdomain]);
});

// Language switch route - bypasses Livewire for full page reload
Route::get('/locale/{locale}', function (Request $request, string $locale) {
    $redirectUrl = $request->query('redirect', $request->header('Referer', '/'));
    
    // Manually do what LanguageSwitch::trigger() does, but with custom redirect URL
    session()->put('locale', $locale);
    cookie()->queue(cookie()->forever('filament_language_switch_locale', $locale));
    event(new LocaleChanged($locale));
    
    return redirect($redirectUrl);
})->name('locale.switch');
