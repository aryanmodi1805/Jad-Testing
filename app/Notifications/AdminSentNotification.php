<?php

namespace App\Notifications;

use App\Channels\FCMChannel;
use App\Messages\FCMMessage;
use App\Models\AdminNotification;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminSentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public AdminNotification $adminNotification;

    /**
     * Create a new notification instance.
     */
    public function __construct(AdminNotification $adminNotification)
    {
        $this->adminNotification = $adminNotification;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        if ($this->adminNotification->is_database) {
            $channels[] = 'database';
        }

        if ($this->adminNotification->is_push) {
            $channels[] = FCMChannel::class;
        }

        return $channels;
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title($this->adminNotification->title)
            ->body($this->adminNotification->body)
            ->getDatabaseMessage();
    }

    /**
     * Get the FCM representation of the notification.
     */
    public function toFCM(object $notifiable): FCMMessage
    {
        return (new FCMMessage())
            ->title($this->adminNotification->title)
            ->text($this->adminNotification->body);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->adminNotification->title,
            'body' => $this->adminNotification->body,
            'admin_notification_id' => $this->adminNotification->id,
        ];
    }
}
