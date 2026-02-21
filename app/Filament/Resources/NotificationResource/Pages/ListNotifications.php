<?php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNotifications extends ListRecords
{
    protected static string $resource = NotificationResource::class;

    public function getTitle(): string
    {
        return __('notification.admin_panel.notifications_management');
    }

    public function getSubheading(): ?string
    {
        return __('notification.admin_panel.view_all_notifications_description');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('notification.admin_panel.send_new_notification'))
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }
}
