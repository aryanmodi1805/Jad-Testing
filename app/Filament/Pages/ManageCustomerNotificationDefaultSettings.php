<?php

namespace App\Filament\Pages;

use App\Settings\CustomerNotificationSettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;

class ManageCustomerNotificationDefaultSettings extends SettingsPage
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = CustomerNotificationSettings::class;
    protected static ?int $navigationSort = 3;

    public function getTitle(): string|Htmlable
    {
        return __('string.notification_settings.manage_customer');
    }

    public static function getNavigationLabel(): string
    {
        return __('string.notification_settings.manage_customer');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('nav.settings');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('Email Notifications')
                    ->label(__('string.notification_settings.email_notifications'))

                    ->schema([
                        Forms\Components\Toggle::make('email_new_message')->label(__('string.notification_settings.customer.email_new_message')),
                        Forms\Components\Toggle::make('email_new_estimate')->label(__('string.notification_settings.customer.email_new_estimate')),
                        Forms\Components\Toggle::make('email_new_response')->label(__('string.notification_settings.customer.email_new_response')),
                        Forms\Components\Toggle::make('email_accepted_invitation')->label(__('string.notification_settings.customer.email_accepted_invitation')),
                        Forms\Components\Toggle::make('email_request_status_change')->label(__('string.notification_settings.customer.email_request_status_change')),
                    ]),
                Forms\Components\Fieldset::make('Push Notifications')
                    ->label(__('string.notification_settings.push_notifications'))
                    ->schema([
                        Forms\Components\Toggle::make('push_new_message')->label(__('string.notification_settings.customer.push_new_message')),
                        Forms\Components\Toggle::make('push_new_estimate')->label(__('string.notification_settings.customer.push_new_estimate')),
                        Forms\Components\Toggle::make('push_new_response')->label(__('string.notification_settings.customer.push_new_response')),
                        Forms\Components\Toggle::make('push_accepted_invitation')->label(__('string.notification_settings.customer.push_accepted_invitation')),
                        Forms\Components\Toggle::make('push_request_status_change')->label(__('string.notification_settings.customer.push_request_status_change')),
                    ]),
            ]);
    }
}
