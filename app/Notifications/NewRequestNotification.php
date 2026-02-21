<?php

namespace App\Notifications;

use App\Channels\FCMChannel;
use App\Filament\Seller\Pages\RequestDetails;
use App\Messages\FCMMessage;
use App\Models\Seller;
use App\Models\SellerNotificationSetting;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected mixed $request;
    private mixed $tenant;

    private $action = null;
    private $action_label = null;
    private $title = null;
    private $body = null;

    public function __construct($request)
    {
        $this->request = $request;
        $this->tenant = $request->country;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via(Seller $notifiable): array
    {
        $this->locale = $notifiable->locale ?? 'ar';

        $this->action = RequestDetails::getUrl([
            'requestId' => $this->request->id,
        ], panel: 'seller', tenant: $this->tenant);

        $this->action_label = __('notification.new_request.action', locale: $this->locale);

        $this->title = __('notification.new_request.title', locale: $this->locale);

        $this->body = __('notification.new_request.body', locale: $this->locale);

        $channels = [];

        /* @var $settings SellerNotificationSetting */
        $settings = $notifiable->notificationSettings;

        if ($settings->email_new_request) {
            $channels[] = 'mail';
        }

        if ($settings->push_new_request) {
            $channels[] = FCMChannel::class;
        }

        return ['database', ...$channels];
    }

    /**
     * Get the database representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
  public function toDatabase(object $notifiable): array
{
    return array_merge(FilamentNotification::make()
        ->title($this->title)
        ->body($this->body)
        ->actions(fn() => $this->action != null ? [
            Action::make(__('notification.new_request.action', locale: $this->locale))
                ->url($this->action)
                ->label($this->action_label)
                ->button(),
        ] : [])
        ->getDatabaseMessage(), [
            'args' => [
                'requestId' => $this->request->id,
            ],
            'screen'=>'request_details'
        ]);
}

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject($this->title)
            ->theme($this->locale == 'ar' ? 'rtl' : 'ltr')
            ->greeting(__('notification.new_request.greeting', ['name' => $notifiable->name], locale: $this->locale))
            ->line($this->body)
            ->action($this->action_label, $this->action);
    }

    public function toFCM(object $notifiable): FCMMessage
    {
        return (new FCMMessage())
            ->title($this->title)
            ->text($this->body)
            ->link($this->action)
            ->screen('request_details')
            ->args([
                'requestId' => $this->request->id,
            ]);

    }
}
