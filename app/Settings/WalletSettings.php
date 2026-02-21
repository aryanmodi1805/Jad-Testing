<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class WalletSettings extends Settings
{
    public float $credit_price;
    public array $supported_currencies;


    public static function group(): string
    {
        return 'wallet';
    }
}
