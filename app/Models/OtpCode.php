<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $code
 * @property string $email
 * @property Carbon $expires_at
 * @method static whereCode(mixed $otp)
 * @method static updateOrCreate(array $array, array $array1)
 * @method static where(string $getOtpField, string $OtpField)
 */
class OtpCode extends Model
{
    use MassPrunable;

    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
        'cooldown_start' => 'datetime',
        'cooldown_end' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('filament-otp-login.table_name'));
    }

    public function prunable(): Builder
    {
        return static::where('expires_at', '<=', now()->subDay()->startOfDay());
    }

    public function isValid(): bool
    {
        return $this->expires_at->isFuture();
    }

    public function leftTime(): int
    {
        return now()->diffInSeconds($this->expires_at);
    }
}
