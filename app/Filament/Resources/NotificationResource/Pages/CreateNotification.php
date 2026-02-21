<?php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use App\Models\AdminNotification;
use App\Models\Customer;
use App\Models\Seller;
use App\Notifications\AdminSentNotification;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateNotification extends CreateRecord
{
    protected static string $resource = NotificationResource::class;
    
    protected static bool $canCreateAnother = false;

    public function getTitle(): string
    {
        return __('notification.admin_panel.send_new_notification');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Create AdminNotification record for tracking
        $adminNotification = AdminNotification::create([
            'title' => $data['title'],
            'body' => $data['body'],
            'recipient_type' => $data['recipient_type'],
            'recipient_ids' => $this->getRecipientIds($data),
            'is_database' => $data['send_database'] ?? true,
            'is_push' => $data['send_push'] ?? false,
            'sent_at' => now(),
            'sent_by' => Auth::id(),
        ]);

        // Get recipient count for display
        $recipientCount = $this->getRecipientCount($data);

        // Dispatch job to send notifications
        \App\Jobs\SendAdminNotificationJob::dispatch($adminNotification);

        // Show success notification
        Notification::make()
            ->title(__('notification.admin_panel.notification_queued_successfully'))
            ->body(__('notification.admin_panel.notification_queued_to_recipients', ['count' => $recipientCount]))
            ->success()
            ->send();

        // Return a dummy notification record for the interface
        return new \Illuminate\Notifications\DatabaseNotification([
            'id' => $adminNotification->id,
            'type' => AdminSentNotification::class,
            'notifiable_type' => 'Multiple',
            'notifiable_id' => 0,
            'data' => [
                'title' => $data['title'],
                'body' => $data['body'],
            ],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function getRecipientCount(array $data): int
    {
        return match ($data['recipient_type']) {
            'all' => Customer::count() + Seller::count(),
            'customers' => Customer::count(),
            'sellers' => Seller::count(),
            'specific_customers' => count($data['customer_ids'] ?? []),
            'specific_sellers' => count($data['seller_ids'] ?? []),
            default => 0
        };
    }

    private function getRecipientIds(array $data): ?array
    {
        return match ($data['recipient_type']) {
            'specific_customers' => $data['customer_ids'] ?? null,
            'specific_sellers' => $data['seller_ids'] ?? null,
            default => null
        };
    }
}
