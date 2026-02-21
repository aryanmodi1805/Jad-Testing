<?php

namespace App\Notifications;

use App\Filament\Actions\RatingAction;
use App\Models\Country;
use App\Models\Customer;
use Filament\Notifications\Actions\Action;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;

class SiteReviewRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct( )
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(mixed $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the database representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title(__('string.notification.title'))
            ->body(__('string.notification.body'))
            ->actions([
                Action::make(__('string.service_rating.notification.action'))
                    ->url(function () use ($notifiable) {
                        $panel = $notifiable instanceof Customer ? 'customer' : 'seller';
                        return "/$panel/?action=rating-action";
                    })
                    ->button(),
            ])
            ->getDatabaseMessage();
    }

    /**
     * Get the broadcast representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return FilamentNotification::make()
            ->title(__('string.notification.title'))
            ->body(__('string.notification.body'))
            ->actions([
                Action::make(__('string.service_rating.notification.action'))
                    ->url(function () use ($notifiable) {
                        $panel = $notifiable instanceof Customer ? 'customer' : 'seller';
                        return "/$panel/?action=rating-action";
                    })
                    ->button(),
            ])
            ->getBroadcastMessage();
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user_id' =>$notifiable->id,
            'user_type' =>$notifiable instanceof Customer ? 'customer' : 'seller',
            'message' => __('string.notification.title'),
        ];
    }
}
