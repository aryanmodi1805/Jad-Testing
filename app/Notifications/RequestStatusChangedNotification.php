<?php
namespace App\Notifications;

use App\Channels\FCMChannel;
use App\Filament\Customer\Resources\RequestResource\Pages\ViewRequest;
use App\Messages\FCMMessage;
use App\Models\Customer;
use App\Models\Request;
use App\Models\Seller;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Request $request;
    private mixed $service;
    private mixed $tenant;

    private $action = null;
    private $action_label = null;
    private $title = null;
    private $body = null;

    public function __construct($request ,   $tenant)
    {
        $this->request = $request;
        $this->service = $request->service;
        $this->tenant = $tenant;

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(Seller | Customer $notifiable): array
    {
        $this->locale = $notifiable->locale ?? 'ar';

        $this->action = ViewRequest::getUrl([
                'record' => $this->request,
            ], panel: 'customer', tenant: $this->tenant);

        $this->action_label = __('notification.request_changed.action', locale: $this->locale);

        $this->title = __('notification.request_changed.title', locale: $this->locale);

        $this->body = __('notification.request_changed.body', [
            'service' => $this->service->getTranslation('name' , $this->locale) , 'status' => $this->request->status->getLabel($this->locale)] , locale: $this->locale);

        $channels = [];

        $settings = $notifiable->notificationSettings;

        if($settings->email_request_status_change) {
            $channels[] = 'mail';
        }

        if ($settings->push_request_status_change){
            $channels[] = FCMChannel::class;
        }

        return ['database' , ...$channels];
    }

    /**
     * Get the database representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase(object $notifiable): array
    {
        return array_merge(FilamentNotification::make()
            ->title($this->title)
            ->body($this->body)
            ->actions(fn() => $this->action != null ?[
                Action::make(__('notification.request_changed.action' ,locale: $this->locale))
                    ->url( $this->action)
                    ->label($this->action_label)
                    ->button(),
            ] : [])
            ->getDatabaseMessage(), [
            'screen'=>'home'
        ]);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject($this->title)
            ->theme($this->locale == 'ar' ? 'rtl' : 'ltr')
            ->greeting(__('notification.request_changed.greeting', ['name' => $notifiable->name] , locale: $this->locale))
            ->line($this->body)
            ->action($this->action_label, $this->action);
    }

    public function toFCM(object $notifiable): FCMMessage
    {
        return (new FCMMessage())
            ->title($this->title)
            ->text($this->body)
            ->link($this->action)
            ->screen('home')
            ->args([
                'action' => 'refresh_requests',
                'requestId' => $this->request->id,
            ]);

    }

}
