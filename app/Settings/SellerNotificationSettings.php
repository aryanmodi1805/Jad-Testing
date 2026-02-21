<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SellerNotificationSettings extends Settings
{
    public bool $email_new_message = false;
    public bool $email_invited = false;
    public bool $email_new_request = false;
    public bool $email_rated = false;
    public bool $email_response_status_change = false;
    public bool $push_new_message = false;
    public bool $push_invited = false;
    public bool $push_new_request = false;
    public bool $push_rated = false;
    public bool $push_response_status_change = false;

    public static function group(): string
    {
        return 'seller_notification';
    }
}
