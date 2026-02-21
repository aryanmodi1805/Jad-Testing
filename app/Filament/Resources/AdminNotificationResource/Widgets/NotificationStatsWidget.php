<?php

namespace App\Filament\Resources\AdminNotificationResource\Widgets;

use App\Models\AdminNotification;
use App\Models\Customer;
use App\Models\Seller;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Notifications\DatabaseNotification;

class NotificationStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalSent = AdminNotification::whereNotNull('sent_at')->count();
        $totalRecipients = Customer::count() + Seller::count();
        $totalNotifications = DatabaseNotification::count();
        $unreadNotifications = DatabaseNotification::whereNull('read_at')->count();
        
        return [
            Stat::make(__('notification.admin_panel.total_sent'), $totalSent)
                ->description(__('notification.admin_panel.admin_notifications_sent'))
                ->descriptionIcon('heroicon-o-paper-airplane')
                ->color('success'),
                
            Stat::make(__('notification.admin_panel.total_recipients'), $totalRecipients)
                ->description(__('notification.admin_panel.customers_and_sellers'))
                ->descriptionIcon('heroicon-o-users')
                ->color('primary'),
                
            Stat::make(__('notification.admin_panel.all_notifications'), $totalNotifications)
                ->description(__('notification.admin_panel.system_wide_notifications'))
                ->descriptionIcon('heroicon-o-bell')
                ->color('info'),
                
            Stat::make(__('notification.admin_panel.unread_notifications'), $unreadNotifications)
                ->description(__('notification.admin_panel.pending_notifications'))
                ->descriptionIcon('heroicon-o-bell-alert')
                ->color($unreadNotifications > 100 ? 'danger' : 'warning'),
        ];
    }
}
