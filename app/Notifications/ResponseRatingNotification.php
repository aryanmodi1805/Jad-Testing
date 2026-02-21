<?php

namespace App\Notifications;

use App\Http\Resources\ResponseResource;
use App\Models\Customer;
use App\Models\Response;
use Filament\Notifications\Actions\Action;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;

class ResponseRatingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /*   @var Response $response */
    protected mixed $response;

    /**
     * Create a new notification instance.
     *
     * @param mixed $response
     * @return void
     */
    public function __construct(mixed $response)
    {
        $this->response = $response;
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
        return array_merge(FilamentNotification::make()
            ->title(__('string.service_rating_notification.title' ,['service_name'=>$this->response->service->name]))
            ->body(__('string.service_rating_notification.body'))
            ->actions([
                Action::make(__('string.service_rating_notification.action'))
                    ->url(function () use ($notifiable) {
                        $panel = $notifiable instanceof Customer ? 'customer' : 'seller';
                        session(['response_id' => $this->response->id]);
                        return "/$panel/?action=rating-service-action";
                    })
                    ->button(),
            ])
            ->getDatabaseMessage(),[
            'screen'=>'home'
        ]);
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
            ->title(__('string.service_rating_notification.title' ,['service_name'=>$this->response->service->name]))
            ->body(__('string.service_rating_notification.body'))
            ->actions([
                Action::make(__('string.service_rating_notification.action'))
                    ->url(function () use ($notifiable) {
                        session(['response_id' => $this->response->id]);
                        $panel = $notifiable instanceof Customer ? 'customer' : 'seller';
                        return "/$panel/?action=rating-service-action";
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
            'response_id' => $this->response->id,
            'message' => __('string.service_rating_notification.title'),
            'link' => route('filament.customer.pages.service-rating-page', ['response' => $this->response->id]),
        ];
    }
}
