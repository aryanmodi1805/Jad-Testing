<?php

namespace App\Channels;

use App\Messages\SmsMessage;
use App\Models\SmsLogs;
use App\Notifications\Sms\SmsClient;
use App\Notifications\Sms\SmsNotification;
use Exception;
use Illuminate\Notifications\Notification;

class SmsChannel
{
    private SmsClient $smsClient;

    public function __construct(SmsClient $smsClient)
    {
        $this->smsClient = $smsClient;
    }

    /**
     * Send the given notification.
     * @throws Exception
     */
    public function send(object $notifiable, SmsNotification $notification): void
    {
        try {
            if (!$to = $notifiable->routeNotificationFor('sms')) {
                throw new Exception('Phone was not found!');
            }

            $message = $notification->toSms($notifiable);

            if (is_string($message)) {
                $message = new SmsMessage($message);
            }

            if (!$message instanceof SmsMessage) {
                throw new Exception('SMS message instance is not correct');
            }

            $to = str_replace('+', '', $to);

            $response = $this->smsClient->send(
                $to,
                $message->content,
            );

            $logsData = [
                'user_id' => $notifiable->id ?? null,
                'MSG_ID' => is_array($response) ? ($response['MSG_ID'] ?? '0') : '0',
                'phone' => $to,
                'sms_text' => $message->content,
                'response' => $response ?? [],
                'status' => 'success'
            ];

            SmsLogs::create($logsData);

        } catch (\Throwable $e) {
            \Log::error('SMS Sending Failed: ' . $e->getMessage());
            
            // Log failure to database if possible
             try {
                SmsLogs::create([
                    'user_id' => $notifiable->id ?? null,
                    'phone' => $notifiable->routeNotificationFor('sms') ?? 'unknown',
                    'sms_text' => 'FAILED',
                    'error_message' => $e->getMessage(),
                    'status' => 'failed',
                     'response' => []
                ]);
            } catch (\Throwable $logException) {
                // If logging to DB fails, just log to file
                \Log::error('SMS Logging Failed: ' . $logException->getMessage());
            }

            throw new Exception($e->getMessage(), 0, $e); // Re-throw as Exception for standard handling
        }
    }


}
