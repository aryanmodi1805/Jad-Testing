<?php

namespace App\Notifications;

use App\Models\Customer;
use App\Models\Request;
use App\Models\Response;
use Filament\Notifications\Actions\Action;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;

class RequestRatingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /*   @var Request $request */
    protected mixed $request;

    /**
     * Create a new notification instance.
     *
     * @param mixed $request
     * @return void
     */
    public function __construct(mixed $request)
    {
        $this->request = $request;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via(mixed $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the database representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title(__('string.request_rating_notification.title' ,['service_name'=>$this->request->service->name,'customer_name'=>$this->request->customer->name]))
            ->body(__('string.request_rating_notification.body'))
            ->actions([
                Action::make(__('string.request_rating_notification.action'))
                    ->url(function () use ($notifiable) {
                        $panel = $notifiable instanceof Customer ? 'customer' : 'seller';
                        session(['request_id' => $this->request->id]);
                        return "/$panel/?action=rating-request-action";
                    })
                    ->button(),
            ])
            ->getDatabaseMessage();
    }

    /**
     * Get the broadcast representation of the notification.
     *
     * @param mixed $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return FilamentNotification::make()
            ->title(__('string.request_rating_notification.title' ,['service_name'=>$this->request->service->name]))
            ->body(__('string.request_rating_notification.body'))
            ->actions([
                Action::make(__('string.request_rating_notification.action'))
                    ->url(function () use ($notifiable) {
                        session(['request_id' => $this->request->id]);
                        $panel = $notifiable instanceof Customer ? 'customer' : 'seller';
                        return "/$panel/?action=rating-request-action";
                    })
                    ->button(),
            ])
            ->getBroadcastMessage();
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable): array
    {
        return [
            'request_id' => $this->request->id,
            'message' => __('string.request_rating_notification.title'),
        ];
    }
}
