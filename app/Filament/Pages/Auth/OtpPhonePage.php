<?php

namespace App\Filament\Pages\Auth;

use App\Forms\Components\OtpInput;
use App\Traits\Auth\HasOTPNotify;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Actions\Action as ActionComponent;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Auth\SessionGuard;
use Illuminate\Support\HtmlString;

class OtpPhonePage extends Page
{

    use HasOTPNotify;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $layout = 'filament-panels::components.layout.simple';
    protected static string $view = 'filament.customer.pages.otp-page';
    protected static bool $shouldRegisterNavigation = false;

    protected ?string $heading = " ";

    public function mount()
    {
        if (Filament::auth()->check() and $this->isPhoneVerified()) {
            return redirect()->intended(Filament::getUrl());
        }

        if (!$this->is_cooldown_mode) {
            $activeCode = $this->getActiveCode();
            if ($activeCode) {
                $this->countDown = $activeCode->leftTime();
                $this->otpCode = $activeCode->code;
                $this->record = $activeCode;
                $this->dispatch('countDownStarted');
            } else {
                $this->sendOtp();
            }
        } else {
            $this->dispatch('startCoolDown');
        }

    }

    public function verifyOtp()
    {
        $this->verifyCode();
        return redirect()->intended(Filament::getUrl());
    }

    public function form(Form $form): Form
    {
        /** @var SessionGuard $authGuard */
        $authGuard = Filament::auth();
        /** @var EloquentUserProvider $provider */
        $provider = $authGuard->getProvider();
        $userModel = $provider->getModel();
        return $form->schema([
            Placeholder::make('change_phone')
                ->content(" ")
                ->key(auth($this->guard)->id())
                ->hiddenLabel()
                ->hintAction(
                    ActionComponent::make('ChangePhoneNumber')
//                        ->size('xs')
                        ->label(__('otp.view.change_phone_number'))
                        ->icon('heroicon-o-pencil-square')
                        ->modalWidth(MaxWidth::Small)
                        ->form([
                            getPhoneInput('phone', $userModel,auth($this->guard)->user())
                                ->default(auth($this->guard)->user()->phone),
                        ])
                        ->action(function ($data) {
                            if (auth($this->guard)->user()->phone != $data['phone']) {
                                auth($this->guard)->user()->update(['phone' => $data['phone']]);
                                $this->dispatch('resendCode');
                            }
                        })
                ),

            Placeholder::make('title')
                ->content(fn() => new HtmlString(' <div class="mb-1"><h1>' . __("otp.notifications.title") . '</h1><hr><p class="dark:text-gray-500 font-light text-sm mt-1">' . $this->notification_body . ' </p> </div>  '))
                ->hiddenLabel(),

            $this->getOtpCodeFormComponent(),

        ])->disabled($this->is_cooldown_mode)->statePath('data');
    }

    protected function getOtpCodeFormComponent(): Component
    {
        return OtpInput::make('otp')->extraAttributes(['style' => 'direction: ltr !important;'])->label(__('otp.otp_code'))
            ->hiddenLabel()
//            ->hintAction($this->goBackAction())
//            ->hint(new HtmlString('<button type="button" wire:click="goBack()" class="focus:outline-none font-bold focus:underline hover:text-primary-400 text-primary-600 text-sm">' . __('otp.view.go_back') . '</button>'))
            ->required();
    }

    public function getOtpFormActions(): array
    {
        return [$this->getSendOtpAction()->disabled($this->is_cooldown_mode),];
    }

    protected function getSendOtpAction(): Action
    {
        return Action::make('send-otp')->label(__('otp.view.verify'))->submit('sendOtp');
    }

    public function getChangePhoneAction(): Action
    {
        return Action::make('send-otp')->label(__('otp.view.verify'))->submit('sendOtp');
    }

    public function hasLogo(): bool
    {
        return true;
    }

    protected function goBackAction(): ActionComponent
    {
        return ActionComponent::make('go-back')
            ->label(__('otp.view.go_back'))
            ->action(fn() => $this->goBack());
    }

    public function goBack(): void
    {
        redirect()->intended(Filament::getUrl());
    }

}
