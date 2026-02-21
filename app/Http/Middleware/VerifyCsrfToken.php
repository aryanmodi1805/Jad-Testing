<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'paymentIpnCallback',
        '/seller/tap-ipn',
        'wallet/*',
        'api/wallet/payment-callback',
        'wallet/payment-callback', // Correct route matching routes/api.php
        'api/seller/wallet/charge',
    ];
}
