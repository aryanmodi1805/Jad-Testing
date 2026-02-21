<?php

namespace App\Livewire\Auth;

use App\Events\LoggedIn;
use App\Exceptions\CooldownOtpException;
use App\Forms\Components\OtpInput;
use App\Models\Seller;
use App\Traits\Auth\InteractWIthOTP;
use Closure;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Placeholder;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;

class SellerOtpLoginPage extends LoginPage
{
    use InteractWIthOTP;

    public bool $otpRequested = false;

    public ?int $pendingSellerId = null;

    public bool $cooldown = false;

    public ?string $cooldownFor = null;

    protected function getAuthenticateFormAction(): Action
    {
        $action = parent::getAuthenticateFormAction();

        return $action->label(fn (): string => $this->otpRequested ? __('otp.view.verify') : __('otp.view.send_code'));
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema($this->getFormSchema())
                    ->statePath('data'),
            ),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            getPhoneInput('phone')
                ->required()
                ->disabled(fn (): bool => $this->otpRequested)
                ->dehydrated(true)
                ->helperText(new HtmlString(__('auth.login_phone_hint'))),
            OtpInput::make('otp')
                ->label(__('otp.otp_code'))
                ->required()
                ->extraAttributes(['style' => 'direction:ltr;'])
                ->hint(new HtmlString(__('otp.view.time_left')))
                ->visible(fn (): bool => $this->otpRequested),
            Placeholder::make('resend')
                ->content(new HtmlString(
                    '<button type="button" wire:click="resendOtp" class="text-primary-600 font-semibold">' .
                    __('otp.view.resend_code') .
                    '</button>'
                ))
                ->visible(fn (): bool => $this->otpRequested),
            Placeholder::make('cooldown')
                ->content(new HtmlString('<p class="text-warning-600">' .
                    __('otp.validation.temporarily_disabled', ['time' => $this->cooldownFor ?? '']) .
                    '</p>'))
                ->visible(fn (): bool => $this->otpRequested && $this->cooldown),
        ];
    }

    public function resendOtp(): void
    {
        if (! $this->pendingSellerId) {
            return;
        }

        $seller = Seller::find($this->pendingSellerId);

        if (! $seller) {
            $this->reset(['otpRequested', 'pendingSellerId']);
            return;
        }

        $this->dispatchOtp($seller);

        Notification::make()
            ->title(__('otp.notifications.title'))
            ->body(new HtmlString(__('otp.notifications.sms_text', [
                'seconds' => config('filament-otp-login.otp_code.expires'),
                'to' => '<span class="font-semibold" style="direction:ltr">' . e($seller->phone) . '</span>',
            ])))
            ->success()
            ->send();
    }

    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();
        $phone = $data['phone'] ?? null;

        if (! $phone) {
            throw ValidationException::withMessages([
                'data.phone' => __('validation.required', ['attribute' => __('cv.phone_number')]),
            ]);
        }

        $seller = Seller::where('phone', $phone)->first();

        if (! $seller) {
            throw ValidationException::withMessages([
                'data.phone' => __('api.account_not_found'),
            ]);
        }

        if (! $this->otpRequested) {
            $this->pendingSellerId = $seller->id;

            try {
                $this->dispatchOtp($seller);
            } catch (ValidationException $exception) {
                $this->pendingSellerId = null;
                throw $exception;
            }

            $this->otpRequested = true;
            $this->resetErrorBag();

            Notification::make()
                ->title(__('otp.notifications.title'))
                ->body(new HtmlString(__('otp.notifications.sms_text', [
                    'seconds' => config('filament-otp-login.otp_code.expires'),
                    'to' => '<span class="font-semibold" style="direction:ltr">' . e($seller->phone) . '</span>',
                ])))
                ->success()
                ->send();

            return null;
        }

        $code = $data['otp'] ?? null;

        if (! $code) {
            throw ValidationException::withMessages([
                'data.otp' => __('validation.required', ['attribute' => __('otp.otp_code')]),
            ]);
        }

        try {
            $this->verifyCode($seller, $code);
        } catch (\Exception $exception) {
            throw ValidationException::withMessages([
                'data.otp' => $exception->getMessage(),
            ]);
        }

        Filament::auth()->login($seller, false);
        session()->regenerate();
        // event(new LoggedIn($seller)); // Assuming LoggedIn event works for sellers too, or create a SellerLoggedIn event if needed. Commenting out for safety if not checked.

        return app(LoginResponse::class);
    }

    protected function dispatchOtp(Seller $seller): void
    {
        try {
            $this->sendOtp($seller);
        } catch (CooldownOtpException $exception) {
            $status = $this->otpStatus($seller);
            $this->cooldown = $status['cooldown'];
            $this->cooldownFor = $status['cooldown_for'];

            throw ValidationException::withMessages([
                'data.phone' => __('otp.validation.temporarily_disabled', ['time' => $this->cooldownFor ?? ''])
            ]);
        } catch (\Exception $exception) {
             \Log::error('Seller Login OTP Dispatch Failed: ' . $exception->getMessage());
             
             // Show a generic error to the user but log the specific one
             throw ValidationException::withMessages([
                 'data.phone' => __('otp.validation.failed_to_send') ?? 'Failed to send verification code. Please try again later.'
             ]);
        }

        $status = $this->otpStatus($seller);
        $this->cooldown = $status['cooldown'];
        $this->cooldownFor = $status['cooldown_for'];
    }

    protected function otpStatus(Seller $seller): array
    {
        $otpRecord = $this->getRecordByAccount($seller);
        $cooldown = $otpRecord !== null && $this->checkCooldown($otpRecord);

        return [
            'expires_at' => $otpRecord?->expires_at,
            'cooldown' => $cooldown,
            'cooldown_for' => $otpRecord !== null && $otpRecord->cooldown_end
                ? $otpRecord->cooldown_end->diffForHumans()
                : null,
        ];
    }
}
