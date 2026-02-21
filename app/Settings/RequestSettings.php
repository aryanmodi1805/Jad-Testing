<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class RequestSettings extends Settings
{
    public bool $request_status;
    public int $maximum_responses;


    public static function group(): string
    {
        return 'request';
    }
}
