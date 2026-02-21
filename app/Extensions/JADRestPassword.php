<?php

namespace App\Extensions;

use Illuminate\Auth\Notifications\ResetPassword as    BaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class JADRestPassword extends  BaseNotification implements ShouldQueue
{
    use Queueable;
    public string $url;

    protected function resetUrl($notifiable): string
    {
        return $this->url;
    }
    public function via($notifiable)
    {
        $this->locale = app()->getLocale()  ;

        return parent::via($notifiable);
    }
    public function toMail($notifiable)
    {
        $resetUrl = $this->resetUrl($notifiable);
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $resetUrl);
        }

        return $this->buildMailMessage($resetUrl);
    }

    }
