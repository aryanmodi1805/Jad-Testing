<?php

namespace App\Notifications;

use App\Models\Response;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResponseApprovedNotification extends Notification
{
    use Queueable;
    protected $response;

    /**
     * Create a new notification instance.
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Response Has Been Approved')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your response to the request "' . $this->response->request->service->name . '" has been approved.')
            ->action('View Response', url('/responses/' . $this->response->id))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'response_id' => $this->response->id,
            'request_id' => $this->response->request->id,
            'message' => 'Your response to the request "' . $this->response->request->service->name . '" has been approved.',
        ];
    }
}
