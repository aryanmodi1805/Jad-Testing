<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AppSettings extends Settings
{
    public bool $ios_app_active;
    public bool $android_app_active;
    public int $ios_min_app_version;
    public int $android_min_app_version;
    public float $minimum_seller_wallet_balance;
    public int $maximum_requests_per_day;
    public int $max_open_pending_requests;

    public static function group(): string
    {
        return 'app';
    }
}
