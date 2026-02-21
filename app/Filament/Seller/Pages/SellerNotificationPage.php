<?php

namespace App\Filament\Seller\Pages;

use App\Models\CustomerNotificationSetting;
use App\Models\Seller;
use App\Models\SellerNotificationSetting;
use App\Settings\CustomerNotificationSettings;
use App\Settings\SellerNotificationSettings;
use Filament\Actions\Action;

use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class SellerNotificationPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.customer.pages.notification-settings';
    public array $data = [];
    public Seller $currentUser;

    public ?SellerNotificationSetting $notificationSetting;

    public function getTitle(): string|Htmlable
    {
        return __('string.notification_settings.settings');
    }

    public function mount()
    {

        $this->currentUser = auth('seller')->user();
        $this->notificationSetting = $this->currentUser->notificationSettings;
        $this->form->fill($this->notificationSetting?->toArray() ?? app(SellerNotificationSettings::class)->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Email Notifications')
                    ->label(__('string.notification_settings.email_notifications'))
                    ->schema([
                        Toggle::make('email_new_message')->label(__('string.notification_settings.seller.email_new_message')),
                        Toggle::make('email_invited')->label(__('string.notification_settings.seller.email_invited')),
                        Toggle::make('email_new_request')->label(__('string.notification_settings.seller.email_new_request')),
                        Toggle::make('email_rated')->label(__('string.notification_settings.seller.email_rated')),
                        Toggle::make('email_response_status_change')->label(__('string.notification_settings.seller.email_response_status_change')),
                    ]),
                Fieldset::make('Push Notifications')
                    ->label(__('string.notification_settings.push_notifications'))

                    ->schema([
                        Toggle::make('push_new_message')->label(__('string.notification_settings.seller.push_new_message')),
                        Toggle::make('push_invited')->label(__('string.notification_settings.seller.push_invited')),
                        Toggle::make('push_new_request')->label(__('string.notification_settings.seller.push_new_request')),
                        Toggle::make('push_rated')->label(__('string.notification_settings.seller.push_rated')),
                        Toggle::make('push_response_status_change')->label(__('string.notification_settings.seller.push_response_status_change')),
                    ])
            ])
            ->columns('full')

            ->statePath('data');
    }

    public function submitAction() : Action
    {
        return Action::make('submit')
            ->label(__('string.save'))
            ->successNotificationTitle(__('filament-actions::edit.single.notifications.saved.title'))
            ->action(function (Action $action){
                $data = $this->form->getState();

                SellerNotificationSetting::updateOrCreate(
                    ['seller_id' => $this->currentUser->id],
                    $data
                );
                $action->success();

            });

    }

}
