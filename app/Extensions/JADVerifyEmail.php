<?php

namespace App\Extensions;

use Illuminate\Auth\Notifications\VerifyEmail as BaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
class JADVerifyEmail extends BaseNotification implements ShouldQueue
{
    use Queueable;

    public string $url;

    protected function verificationUrl($notifiable): string
    {
        return $this->url;
    }
    public function via($notifiable)
    {
        $this->locale = $notifiable->locale;

        return parent::via($notifiable);
    }
}
