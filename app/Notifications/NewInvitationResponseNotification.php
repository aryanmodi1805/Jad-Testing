<?php
namespace App\Notifications;

use App\Channels\FCMChannel;
use App\Filament\Customer\Resources\RequestResource\Pages\ViewRequest;
use App\Filament\Seller\Pages\MyResponses;
use App\Messages\FCMMessage;
use App\Models\Customer;
use App\Models\Seller;
use App\Models\SellerNotificationSetting;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewInvitationResponseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected mixed $request;
    protected mixed $response;
    private mixed $from;
    private mixed $service;
    private mixed $tenant;

    private $action = null;
    private $action_label = null;
    private $title = null;
    private $body = null;

    public function __construct($request , $response,  $from , $tenant)
    {
        $this->request = $request;
        $this->response = $response;
        $this->service = $request->service;
        $this->tenant = $tenant;
        $this->from = $from;

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(Seller $notifiable): array
    {
        $this->locale = $notifiable->locale ?? 'ar';

        $this->action = MyResponses::getUrl([
            'responseId' => $this->response->id,
        ],panel: 'seller', tenant: $this->tenant);

        $this->action_label = __('notification.new_invitation.action', locale: $this->locale);

        $this->title = __('notification.new_invitation.title', locale: $this->locale);

        $this->body = __('notification.new_invitation.body', ['customer' => ($this->from->name),
            'service' => $this->service->getTranslation('name' , $this->locale) ] , locale: $this->locale);

        $channels = [];

        /* @var $settings SellerNotificationSetting*/
        $settings = $notifiable->notificationSettings;

        if($settings->email_invited) {
            $channels[] = 'mail';
        }

        if ($settings->push_invited){
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
                Action::make(__('notification.new_invitation.action' ,locale: $this->locale))
                    ->url( $this->action)
                    ->label($this->action_label)
                    ->button(),
            ] : [])
            ->getDatabaseMessage(),[
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
            ->greeting(__('notification.new_invitation.greeting', ['name' => $notifiable->name] , locale: $this->locale))
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
