<?php

namespace App\Filament\Customer\Pages;

use App\Models\Customer;
use App\Models\CustomerNotificationSetting;
use App\Settings\CustomerNotificationSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class CustomerNotificationPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.customer.pages.notification-settings';
    public array $data = [];
    public Customer $currentUser;

    public ?CustomerNotificationSetting $notificationSetting;

    public function getTitle(): string|Htmlable
    {
        return __('string.notification_settings.settings');
    }

    public function mount()
    {

        $this->currentUser = auth('customer')->user();
        $this->notificationSetting = $this->currentUser->notificationSettings;
        $this->form->fill($this->notificationSetting?->toArray() ?? app(CustomerNotificationSettings::class)->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Email Notifications')
                    ->label(__('string.notification_settings.email_notifications'))

                    ->schema([
                        Toggle::make('email_new_message')->label(__('string.notification_settings.customer.email_new_message')),
                        Toggle::make('email_new_estimate')->label(__('string.notification_settings.customer.email_new_estimate')),
                        Toggle::make('email_new_response')->label(__('string.notification_settings.customer.email_new_response')),
                        Toggle::make('email_accepted_invitation')->label(__('string.notification_settings.customer.email_accepted_invitation')),
                        Toggle::make('email_request_status_change')->label(__('string.notification_settings.customer.email_request_status_change')),
                    ]),
                Fieldset::make('Push Notifications')
                    ->label(__('string.notification_settings.push_notifications'))
                    ->schema([
                        Toggle::make('push_new_message')->label(__('string.notification_settings.customer.push_new_message')),
                        Toggle::make('push_new_estimate')->label(__('string.notification_settings.customer.push_new_estimate')),
                        Toggle::make('push_new_response')->label(__('string.notification_settings.customer.push_new_response')),
                        Toggle::make('push_accepted_invitation')->label(__('string.notification_settings.customer.push_accepted_invitation')),
                        Toggle::make('push_request_status_change')->label(__('string.notification_settings.customer.push_request_status_change')),
                    ]),
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

                CustomerNotificationSetting::updateOrCreate(
                    ['customer_id' => $this->currentUser->id],
                    $data
                );
                $action->success();

            });

    }

}
