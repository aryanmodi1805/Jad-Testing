<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;

class SellerResponseNotification extends Notification implements ShouldQueue
{
    use Queueable;

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
        return ['broadcast'];
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
            ->title(__('responses.new_response_title'))
            ->body(__('responses.new_response_body'))
//            ->actions([
//                Action::make('View Response')
////                    ->url(route('requests.show', $this->response->request_id))
//                    ->button(),
//            ])
            ->getDatabaseMessage(), [
            'screen' => 'home'
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
            ->title(__('responses.new_response_title'))
            ->body(__('responses.new_response_body'))
//            ->actions([
//                Action::make('View Response')
////                    ->url(route('requests.show', $this->response->request_id))
//                    ->button(),
//            ])
            ->getBroadcastMessage();
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'response_id' => $this->response->id,
            'request_id' => $this->response->request_id,
            'message' => __('responses.new_response_message'),
            'link' => route('requests.show', $this->response->request_id),
        ];
    }
}
