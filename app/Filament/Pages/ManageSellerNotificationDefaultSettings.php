<?php

namespace App\Filament\Pages;

use App\Settings\SellerNotificationSettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;

class ManageSellerNotificationDefaultSettings extends SettingsPage
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = SellerNotificationSettings::class;
    protected static ?int $navigationSort = 2;

    public function getTitle(): string|Htmlable
    {
        return __('string.notification_settings.manage_seller');
    }

    public static function getNavigationLabel(): string
    {
        return __('string.notification_settings.manage_seller');
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
                        Forms\Components\Toggle::make('email_new_message')->label(__('string.notification_settings.seller.email_new_message')),
                        Forms\Components\Toggle::make('email_invited')->label(__('string.notification_settings.seller.email_invited')),
                        Forms\Components\Toggle::make('email_new_request')->label(__('string.notification_settings.seller.email_new_request')),
                        Forms\Components\Toggle::make('email_rated')->label(__('string.notification_settings.seller.email_rated')),
                        Forms\Components\Toggle::make('email_response_status_change')->label(__('string.notification_settings.seller.email_response_status_change')),
                    ]),
                Forms\Components\Fieldset::make('Push Notifications')
                    ->label(__('string.notification_settings.push_notifications'))

                    ->schema([
                        Forms\Components\Toggle::make('push_new_message')->label(__('string.notification_settings.seller.push_new_message')),
                        Forms\Components\Toggle::make('push_invited')->label(__('string.notification_settings.seller.push_invited')),
                        Forms\Components\Toggle::make('push_new_request')->label(__('string.notification_settings.seller.push_new_request')),
                        Forms\Components\Toggle::make('push_rated')->label(__('string.notification_settings.seller.push_rated')),
                        Forms\Components\Toggle::make('push_response_status_change')->label(__('string.notification_settings.seller.push_response_status_change')),
                    ])
            ]);
    }
}
