<?php

namespace App\Livewire;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Jeffgreco13\FilamentBreezy\Actions\PasswordButtonAction;
use Jeffgreco13\FilamentBreezy\Livewire\TwoFactorAuthentication;
use Jeffgreco13\FilamentBreezy\Pages\TwoFactorPage;
use Livewire\Component;

class CustomTwoFactorPage extends TwoFactorAuthentication
{
    public function mount()
    {
        $this->user = Filament::getCurrentPanel()->auth()->user();
    }
    public function enableAction(): Action
    {
        return PasswordButtonAction::make('enable')
            ->label(__('filament-breezy::default.profile.2fa.actions.enable'))
            ->form([
                TextInput::make('current_password')
                    ->label(__('filament-breezy::default.password_confirm.current_password'))
                    ->required()
                    ->password()
                    ->revealable()
                    ->rule("current_password:" . filament('filament-breezy')->getCurrentPanel()->getAuthGuard()),
            ])
            ->action(function () {
                // sleep(1);
                $this->user->enableTwoFactorAuthentication();
                Notification::make()
                    ->success()
                    ->title(__('filament-breezy::default.profile.2fa.enabled.notify'))
                    ->send();
            });
    }
}
