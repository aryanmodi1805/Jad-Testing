<?php

namespace App\Traits\Auth;

use App\Models\OtpCode;
use App\Models\User;
use App\Notifications\SendOtpCode;
use App\Notifications\Sms\SmsNotification;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;

trait HasOTPNotify
{
    use InteractsWithFormActions, Notifiable;
    use WithRateLimiting;

    public ?array $data = [];
    public string $email = '';
    public string $phone = '';
    public string $OtpField = 'phone';
    public int $countDown = 120;
    public int|string $otpCode = '';
    public int $maxAttempts = 3;
    public int|float $cooldownDuration = 5; // minutes

    public null|OtpCode $record;
    public bool $is_cooldown_mode = false;
    public string $notification_body = "";
    public string $guard = '';
    public $cooldown_end_at = "";
    protected Authenticatable|null|User $user = null;

    private string $channel = 'sms';

    public function __construct()
    {

        $this->countDown = config('filament-otp-login.otp_code.expires');
        $this->maxAttempts = config('filament-otp-login.attempt_limit.maxAttempts');
        $this->cooldownDuration = config('filament-otp-login.attempt_limit.cooldownDuration');
        $this->guard = filament()->getCurrentPanel()->getAuthGuard();
        $this->user = auth($this->guard)->user();
        $this->email = $this->user->email ?? "";
        $this->phone = $this->user->phone ?? "";
        $optCol = $this->getOtpField();
        $this->OtpField = $this->user->$optCol ?? "";
        $this->notification_body = __('otp.notifications.sms_text', ['seconds' => config('filament-otp-login.otp_code.expires'),
            'to' => '<span class="font-semibold" style="direction: ltr">' .str_replace( '+','', $this->OtpField ). '</span>'
        ]);
        $this->is_cooldown_mode = $this->isIsCooldownMode();
        $this->channel = config('filament-otp-login.channel');
    }

    private function getOtpField(): string
    {
        return config('filament-otp-login.otp_colum_name', 'email');
    }

    public function isIsCooldownMode(): bool
    {
        $code = OtpCode::where($this->getOtpField(), $this->OtpField)
            ->whereNotNull('cooldown_start')
            ->whereNotNull('cooldown_end')
            ->where('notifiable_type', get_class(auth($this->guard)->user()))
            ->where('notifiable_id', auth($this->guard)->id())
            ->first();
        $this->is_cooldown_mode = $code && (now()->lessThanOrEqualTo($code->cooldown_end));

        return $this->is_cooldown_mode;
    }



    public function sendOtp(): void
    {
//        $this->rateLimiter();
        $this->generateCode();
        $this->sendOtpToUser($this->otpCode);
    }

    protected function rateLimiter()
    {
        try {
            $this->rateLimit(3);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();
            return null;
        }
    }

    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return Notification::make()
            ->title(__('filament-panels::pages/auth/login.notifications.throttled.title', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]))
            ->body(array_key_exists('body', __('filament-panels::pages/auth/login.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/login.notifications.throttled.body', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]) : null)
            ->danger();
    }

    public function generateCode(): void
    {
        if ($this->isIsCooldownMode()) {
            $this->dispatch('startCoolDown');
            return;
        }
        do {
            $length = config('filament-otp-login.otp_code.length');

            $code = str_pad(rand(0, 10 ** $length - 1), $length, '0', STR_PAD_LEFT);
        } while (OtpCode::whereCode($code)->where($this->getOtpField(), $this->OtpField)->exists());

        $this->otpCode = $code;
        $this->record = OtpCode::updateOrCreate([
            'channel' => $this->channel,
            'notifiable_type' => get_class(auth($this->guard)->user()),
            'notifiable_id' => auth($this->guard)->id(),
        ], [
            'email' => $this->email,
            'phone' => $this->phone,
            'code' => $this->otpCode,
            'expires_at' => now()->addSeconds(config('filament-otp-login.otp_code.expires')),

        ]);


        $this->dispatch('countDownStarted');
    }

    protected function sendOtpToUser(string $otpCode): void
    {
        $this->notification_body = __('otp.notifications.body',
            ['seconds' => config('filament-otp-login.otp_code.expires'), 'to' => $this->OtpField]);

        if ($this->channel == 'sms') {
            $sms_text = __('otp.mail.line1', ['code' => $otpCode]) . ' .' . __('otp.mail.line2', ['seconds' => config('filament-otp-login.otp_code.expires')]);
            $this->notify(new SmsNotification($sms_text));
            $this->notification_body = __('otp.notifications.sms_text', ['seconds' => config('filament-otp-login.otp_code.expires'),
                'to' => '<span class="font-semibold" style="direction: ltr">' .str_replace( '+','', $this->OtpField ). '</span>'
            ]);

        } else
            $this->notify(new SendOtpCode($otpCode));// send to email

        // cooldown start
        $this->coolDownUpdate();
        // end cooldown
        Notification::make()
            ->title(__('otp.notifications.title'))
            ->body(new HtmlString($this->notification_body))
            ->success()
//            ->persistent()
            ->send();
    }

    public function coolDownUpdate(): void
    {

        if (fmod($this->record->send_attempts, $this->maxAttempts) == 0) {

            $this->record->update([
                'cooldown_start' => now(),
                'cooldown_end' => now()->addMinutes($this->record->send_attempts  * $this->cooldownDuration)
            ]);
        }

        $this->record->increment('send_attempts');


    }

    public function verifyCode(): void
    {
        $code = $this->getCode();
        $this->rateLimiter();
        if (!$code) {
            $this->incrementFailedAttempt($this->getUserCode());
            throw ValidationException::withMessages([
                'data.otp' => __('otp.validation.invalid_code'),
            ]);
        } elseif (!$code->isValid()) {
            $this->incrementFailedAttempt();
            throw ValidationException::withMessages([
                'data.otp' => __('otp.validation.expired_code'),
            ]);
        } else {
            $user = auth($this->guard)->user();
            $user->update(['phone_verified_at' => now()]);
            $this->dispatch('codeVerified');
            $code->delete();
        }
    }

    protected function getCode()
    {
        $this->record = OtpCode::whereCode($this->data['otp'])->where($this->getOtpField(), $this->OtpField)
            ->where('notifiable_type', get_class(auth($this->guard)->user()))
            ->where('notifiable_id', auth($this->guard)->id())
            ->first();
        return $this->record;
    }

    public function incrementFailedAttempt($record = null): void
    {
        empty($record) ? $this->record->increment('failed_attempts') :
            $record->increment('failed_attempts');
    }

    protected function getUserCode()
    {
        return OtpCode::where($this->getOtpField(), $this->OtpField)
            ->where('notifiable_type', get_class(auth($this->guard)->user()))
            ->where('notifiable_id', auth($this->guard)->id())
            ->first();
    }

    #[On('resendCode')]
    public function resendCode(): void
    {
        redirect(request()->header('Referer'));
//        $this->generateCode();
//        $this->sendOtpToUser($this->otpCode);
    }

    #[On('startCoolDown')]
    public function startCoolDown(): void
    {
        $this->is_cooldown_mode = true;
    }

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function getCooldownDuration(): float|int
    {
        return $this->cooldownDuration;
    }

    public function routeNotificationForSms(): string
    {
        return $this->phone;
    }

    protected function getActiveCode()
    {
        return OtpCode::
        where('notifiable_type', get_class(auth($this->guard)->user()))
            ->where('notifiable_id', auth($this->guard)->id())
            ->where($this->getOtpField(), $this->OtpField)
            ->where('expires_at', '>', now())->first();
    }

    private function isPhoneVerified(): bool
    {
        return !empty($this->user->phone_verified_at);
    }
}
