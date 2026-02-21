<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CustomerNotificationSettings extends Settings
{
    public bool $email_new_message = false;
    public bool $email_new_estimate = false;
    public bool $email_new_response = false;
    public bool $email_accepted_invitation = false;
    public bool $email_request_status_change = false;
    public bool $push_new_message = false;
    public bool $push_new_estimate = false;
    public bool $push_new_response = false;
    public bool $push_accepted_invitation = false;
    public bool $push_request_status_change = false;

    public static function group(): string
    {
        return 'customer_notification';
    }
}
