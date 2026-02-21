<?php
namespace App\Notifications;

use App\Channels\FCMChannel;
use App\Filament\Customer\Resources\RequestResource\Pages\ViewRequest;
use App\Filament\Seller\Pages\MyResponses;
use App\Http\Resources\ResponseResource;
use App\Messages\FCMMessage;
use App\Models\Customer;
use App\Models\Estimate;
use App\Models\Seller;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewEstimateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected mixed $estimate;

    protected mixed $request;
    private mixed $from;
    private mixed $service;
    private mixed $tenant;

    private $action = null;
    private $action_label = null;
    private $title = null;
    private $body = null;

    public function __construct(Estimate $estimate , $request ,  $from , $tenant)
    {
        $this->estimate = $estimate;
        $this->request = $request;
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
    public function via(Customer $notifiable): array
    {
        $this->locale = $notifiable->locale ?? 'ar';

        $this->action = ViewRequest::getUrl([
                'record' => $this->request,
            ], panel: 'customer', tenant: $this->tenant);

        $this->action_label = __('notification.new_estimate.action', locale: $this->locale);

        $this->title = __('notification.new_estimate.title', locale: $this->locale);

        $this->body = __('notification.new_estimate.body', ['seller' => (filled($this->from->getTranslation('company_name' , $this->locale)) ? $this->from->getTranslation('company_name' , $this->locale): $this->from->name),
            'service' => $this->service->getTranslation('name' , $this->locale) ] , locale: $this->locale);

        $channels = [];

        $settings = $notifiable->notificationSettings;

        if($settings->email_new_estimate) {
            $channels[] = 'mail';
        }

        if ($settings->push_new_message){
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
                Action::make(__('notification.new_estimate.action' ,locale: $this->locale))
                    ->url( $this->action)
                    ->label($this->action_label)
                    ->button(),
            ] : [])
            ->getDatabaseMessage(),[
            'args' => [
                'response'=> ResponseResource::make($this->estimate->response),
                'request'=> ResponseResource::make($this->request),
                'isCustomer'=> true,
            ],
            'screen'=>'chat'
        ]);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject($this->title)
            ->theme($this->locale == 'ar' ? 'rtl' : 'ltr')
            ->greeting(__('notification.new_estimate.greeting', ['name' => $notifiable->name] , locale: $this->locale))
            ->line($this->body)
            ->action($this->action_label, $this->action);
    }

    public function toFCM(object $notifiable): FCMMessage
    {
        return (new FCMMessage())
            ->title($this->title)
            ->text($this->body)
            ->link($this->action)
            ->screen('chat')
            ->args([
                'response'=> ResponseResource::make($this->estimate->response),
                'request'=> ResponseResource::make($this->request),
                'isCustomer'=> true,
            ]);

    }

//    /**
//     * Get the broadcast representation of the notification.
//     *
//     * @param  mixed  $notifiable
//     * @return BroadcastMessage
//     */
//    public function toBroadcast(object $notifiable): BroadcastMessage
//    {
//        return FilamentNotification::make()
//            ->title($this->title)
//            ->body($this->body)
//            ->actions(fn() => $this->action != null ?[
//                Action::make(__('notification.new_message.action', locale: $this->locale))
//                    ->url( $this->action)
//                    ->label($this->action_label)
//                    ->button(),
//            ] : [])
//            ->getBroadcastMessage();
//
//    }

}
