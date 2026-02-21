<?php

namespace App\Channels;

use App\Messages\FCMMessage;
use App\Notifications\NewMessageNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\WebPushConfig;
use Kreait\Firebase\Messaging\Notification as FCMNotification;

class FCMChannel
{
    /**
     * Send the given notification.
     */
    public FCMMessage $message;
    public function send(object $notifiable, Notification $notification): void
    {
        /** @var Messaging $messaging */
        /** @var NewMessageNotification $notification */

        $messaging = app('firebase.messaging');
        $tokens = $notifiable->tokens;

        $this->message = $notification->toFCM($notifiable);

        // Encode args as JSON string if it's an array or object
        $args = is_array($this->message->args) || is_object($this->message->args)
            ? json_encode($this->message->args)
            : (string)$this->message->args;

        // Ensure screen is a string
        $screen = (string)$this->message->screen;

        $config = WebPushConfig::fromArray([
            'data' => [
                'url' =>  $this->message->link,
                'lang' => 'ar',
                'title' => $this->message->title,
                'body' => $this->message->text,
                'icon' => asset('assets/logo/icon.png'),
                'image' => asset('assets/logo/logo.png'),
                'dir' => 'rtl',
                "action" => $this->message->link,
                "action_title" => __('string.show' , locale: $this->message->locale)
            ],
            'notification' => [
                'actions' => [
                    [
                        "action" => "view",
                        "title" => __('string.show' , locale: $this->message->locale)
                    ],
                ],
                'title' => $this->message->title,
                'body' => $this->message->text,
                'icon' => asset('assets/logo/icon.png'),
                'image' => asset('assets/logo/logo.png'),
                'requireInteraction' => true,
                'action_title' => __('string.show' , locale: $this->message->locale),
                'data' => [
                    'url' =>  $this->message->link,
                ]

            ],
            'fcm_options' => [
                'link' =>  $this->message->link,
            ],

        ]);

        $androidConfig = AndroidConfig::fromArray([
            'ttl' => '3600s',
            'priority' => 'high',
            'notification' => [
                'title' => $this->message->title,
                'body' => $this->message->text,
                'icon' => asset('assets/logo/icon.png'),
                'color' => '#f45342',
                'sound' => 'default',
            ],
        ]);

        $message = CloudMessage::new()
            ->withWebPushConfig($config)

            ->withAndroidConfig($androidConfig)
            ->withData([
                'screen' => $screen,
                'args' => $args,
            ])

            ->withDefaultSounds()->withHighestPossiblePriority();
        try {
            $messaging->sendMulticast($message, $tokens);
        } catch (\Exception|\Throwable $exception) {
            Log::error($exception);
        }

    }


}
