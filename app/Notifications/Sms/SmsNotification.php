<?php

namespace App\Notifications\Sms;

use App\Channels\SmsChannel;
use App\Messages\SmsMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private string $sms_text;

    /**
     * Create a new notification instance.
     */
    public function __construct($sms_text)
    {
        $this->sms_text = $sms_text;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [
            SmsChannel::class
        ];
    }

    /**
     * Get the Sms representation of the notification.
     */
    public function toSms(object $notifiable): SmsMessage
    {
        return (new SmsMessage())
            ->content($this->sms_text);
    }


}
