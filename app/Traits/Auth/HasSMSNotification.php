<?php

namespace App\Traits\Auth;

use App\Models\OtpCode;
use App\Notifications\SendOtpCode;
use App\Notifications\Sms\SmsNotification;
use Illuminate\Validation\ValidationException;

trait HasSMSNotification
{
    public function routeNotificationForSms(): string
    {
        return $this->phone;
    }

}
