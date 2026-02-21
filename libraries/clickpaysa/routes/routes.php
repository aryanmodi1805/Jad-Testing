<?php

use Clickpaysa\Laravel_package\Controllers\ClickpayLaravelListenerApi;

Route::post('/seller/paymentIPN', [ClickpayLaravelListenerApi::class, 'paymentIPN'])->name('payment_ipn');
Route::post('/paymentIpnCallback', [ClickpayLaravelListenerApi::class, 'paymentIpnCallback'])->name('payment_ipn.callback');
