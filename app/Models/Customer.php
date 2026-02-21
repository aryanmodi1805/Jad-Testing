<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Observers\CustomerObserver;
use App\Traits\Auth\HasSMSNotification;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use TomatoPHP\FilamentFcm\Traits\InteractsWithFCM;

#[ObservedBy(CustomerObserver::class)]
class Customer extends Authenticate implements FilamentUser, HasTenants , HasAvatar
{
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable, HasSMSNotification;
    use softDeletes;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'blocked',
        'phone_verified_at',
        'blocked',
        'avatar_url',
        'email_verified_at',
        'phone',
        'country_id',
        'tokens',
        'locale',
        "rate",
        "rate_count",
        'seller_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'blocked' => 'boolean',
            'tokens' => 'array',
        ];
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return !$this->blocked;
    }

    public function associatedAccount(): BelongsTo
    {
        return $this->belongsTo(Seller::class,'seller_id');
    }
    public function blockReport(): MorphMany
    {
        return $this->morphMany(BlockReport::class, 'blocked');
    }

    public function blocks(): MorphMany
    {
        return $this->morphMany(BlockReport::class, 'user');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class, 'customer_id');
    }

    public function routeNotificationForSms(): string
    {

        return $this->phone;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $tenant->id == $this->country_id;
    }

    public function getTenants(Panel $panel): array|Collection
    {
        return $this->country()->get();
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class)->active();
    }

    public function averageRating()
    {
        return $this->ratings()->avg('rating') ?? 0;
    }

    public function ratings()
    {
        return $this->hasManyThrough(Rating::class, Request::class, 'customer_id', 'rateable_id')
            ->where('rateable_type', Request::class)->latest();
    }

    public function ratingsCount(): int
    {
        return $this->ratings()->count() ?? 0;
    }

    public function getIsPhoneVerifiedAttribute(): bool
    {
        return !is_null($this->phone_verified_at);
    }


    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? Storage::url($this->avatar_url) : null ;

    }

    public function scopeCountry(Builder $query): Builder
    {
        return $query->where('country_id', getCountryId());
    }

    public function notificationSettings(): HasOne
    {
        return $this->hasOne(CustomerNotificationSetting::class, 'customer_id');
    }


    public function updateRating(): void
    {
        $this->update([
            'rate' => $this->averageRating(),
            'rate_count' => $this->ratingsCount()
        ]);
    }
}
