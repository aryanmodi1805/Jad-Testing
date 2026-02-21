<?php

namespace App\Settings;

use Illuminate\Support\Facades\Storage;
use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $default_country;

    public bool $request_status;

    public int $maximum_responses;

    public int $fast_response_badge;
    public int $regular_customer_badge;

    public bool $show_subscriptions_page;

    public int $teams_count;
    public int $customers_count;
    public int $projects_completed;

    public static function group(): string
    {
        return 'default';
    }
}
