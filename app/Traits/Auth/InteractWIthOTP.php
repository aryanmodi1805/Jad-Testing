<?php

namespace App\Traits\Auth;

use App\Exceptions\CooldownOtpException;
use App\Models\OtpCode;
use App\Models\User;
use App\Notifications\SendOtpCode;
use App\Notifications\Sms\SmsNotification;
use Carbon\Carbon;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;

trait InteractWIthOTP
{
    public function isIsCooldownMode($notifiable): bool
    {
        $code = OtpCode::where($this->getOtpFieldColumn(), $this->getOtpFieldValue($notifiable))
            ->whereNotNull('cooldown_start')
            ->whereNotNull('cooldown_end')
            ->where('notifiable_type', get_class($notifiable))
            ->where('notifiable_id', $notifiable->id)
            ->first();

        return $this->checkCooldown($code);
    }

    public function checkCooldown(?OtpCode $otp): bool
    {
        return $otp != null && $otp->cooldown_end != null && now()->lessThanOrEqualTo($otp->cooldown_end);

    }

    /**
     * @throws CooldownOtpException
     */
    public function sendOtp($notifiable): void
    {
        if ($this->isIsCooldownMode($notifiable)) {
            throw new CooldownOtpException();
        }

        $codeRecord = $this->generateCode($notifiable);

        $this->sendOtpToUser($notifiable, $codeRecord->code);
    }

    public function generateCode($notifiable): OtpCode
    {
        do {
            $length = $this->getOtpLength();
            $code = str_pad(rand(0, 10 ** $length - 1), $length, '0', STR_PAD_LEFT);
        } while (OtpCode::whereCode($code)->where($this->getOtpFieldColumn(), $this->getOtpFieldValue($notifiable))->exists());



        return OtpCode::updateOrCreate([
            'channel' => $this->getOtpChannel(),
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->id,
        ], [
            'email' => $notifiable->email,
            'phone' => $notifiable->phone,
            'code' => $code,
            'expires_at' => now()->addSeconds(config('filament-otp-login.otp_code.expires')),
        ]);
    }

    protected function sendOtpToUser($notifiable, string $otpCode): void
    {
        if ($this->getOtpChannel() == 'sms') {
            $sms_text = __('otp.mail.line1', ['code' => $otpCode]) . ' .' . __('otp.mail.line2', ['seconds' => $this->getExpiredTime()]);
            $notifiable->notify(new SmsNotification($sms_text));
        } else {
            $notifiable->notify(new SendOtpCode($otpCode));// send to email
        }
        $this->coolDownUpdate($notifiable,$otpCode);
    }

    public function coolDownUpdate($notifiable, string $otpCode): void
    {
        $record = $this->getRecord($notifiable, $otpCode);

        if (fmod($record->send_attempts, $this->getMaxAttempts()) == 0) {
            $record->update([
                'cooldown_start' => now(),
                'cooldown_end' => now()->addMinutes($record->send_attempts  * $this->getCooldownDuration())
            ]);
        }
        $record->increment('send_attempts');
    }

    public function getRecord($notifiable, string $otpCode){
        return OtpCode::query()->where('code', $otpCode)
            ->where('notifiable_type', get_class($notifiable))
            ->where('notifiable_id', $notifiable->id)->first();
    }


    public function getRecordByAccount($notifiable){
        return OtpCode::query()
            ->where('notifiable_type', get_class($notifiable))
            ->where('notifiable_id', $notifiable->id)->first();
    }

    /**
     * @throws \Exception
     */
    public function verifyCode($notifiable , $otpCode): void
    {
        $code = $this->getRecord($notifiable,$otpCode);

        if (!$code) {
            $this->incrementFailedAttempt($this->getUserCode($notifiable));
            throw new \Exception(__('otp.validation.invalid_code'));
        } elseif (!$code->isValid()) {
            $this->incrementFailedAttempt();
            throw new \Exception(__('otp.validation.expired_code'));
        } else {
            $notifiable->update(['phone_verified_at' => now()]);
            $code->delete();
        }
    }

    public function incrementFailedAttempt($record = null): void
    {
        if($record != null) {
            $record->increment('failed_attempts');
        }
    }

    public function getUserCode($notifiable) : ?OtpCode
    {
        return OtpCode::where($this->getOtpFieldColumn(), $this->getOtpFieldValue($notifiable))
            ->where('notifiable_type', get_class($notifiable))
            ->where('notifiable_id', $notifiable->id)
            ->first();
    }


    public function getActiveCode($notifiable): ?OtpCode
    {
        return OtpCode::where('notifiable_type', get_class($notifiable))
            ->where('notifiable_id', $notifiable->id)
            ->where($this->getOtpFieldColumn(), $this->getOtpFieldValue($notifiable))
            ->where('expires_at', '>', now())->first();
    }

    public function getCountDown(): int
    {
        return config('filament-otp-login.otp_code.expires', 120);
    }

    public function getMaxAttempts(){
        return config('filament-otp-login.attempt_limit.maxAttempts', 3);
    }

    public function getCooldownDuration(){
        return config('filament-otp-login.attempt_limit.cooldownDuration', 5);
    }

    public function getOtpChannel()
    {
        return config('filament-otp-login.channel','sms');
    }

    public function getOtpFieldColumn(): string
    {
        return config('filament-otp-login.otp_colum_name', 'phone');
    }

    public function getOtpLength(): int
    {
        return config('filament-otp-login.otp_code.length', 6);
    }

    public function getExpiredTime(): int
    {
        return config('filament-otp-login.otp_code.expires', 120);
    }

    public function getOtpFieldValue($notifiable): string
    {
        return $notifiable->{$this->getOtpFieldColumn()} ?? "";
    }

}
