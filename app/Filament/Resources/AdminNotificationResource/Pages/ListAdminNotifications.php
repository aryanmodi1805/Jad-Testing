<?php

namespace App\Filament\Resources\AdminNotificationResource\Pages;

use App\Filament\Resources\AdminNotificationResource;
use App\Filament\Resources\NotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdminNotifications extends ListRecords
{
    protected static string $resource = AdminNotificationResource::class;

    public function getTitle(): string
    {
        return __('notification.admin_panel.sent_notifications_history');
    }

    public function getSubheading(): ?string
    {
        return __('notification.admin_panel.track_admin_notifications');
    }

    protected function getHeaderWidgets(): array
    {
        return AdminNotificationResource::getWidgets();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('send_new')
                ->label(__('notification.admin_panel.send_new_notification'))
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->url(NotificationResource::getUrl('create')),
        ];
    }
}
