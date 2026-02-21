<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Archilex\AdvancedTables\Concerns\HasViews;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasDefaultTenant;
use Filament\Models\Contracts\HasTenants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Filament\Panel;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticate implements FilamentUser, HasTenants, HasDefaultTenant ,HasAvatar
{
    use HasApiTokens, HasFactory, Notifiable,HasRoles , TwoFactorAuthenticatable;
    use HasViews;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'last_country_id',
        'tokens','locale',
        'email_verified_at',
        'avatar_url'
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

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'tokens' => 'array'
    ];

    public function lastCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'last_country_id');
    }
    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'user_countries');
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->countries;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        $canAccess = $this->countries->contains($tenant);

        if($canAccess){
            $this->last_country_id = $tenant->id;
            $this->save();
        }

        return $canAccess;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function getDefaultTenant(Panel $panel): ?Model
    {
        $lastCountry = $this->lastCountry;

        if($lastCountry?->slug??"" == getSubdomain()){
            return $lastCountry;
        }else{
            $subDomainTenant  = getCurrentTenant();

            if($this->countries->contains($subDomainTenant)){
                return $subDomainTenant;
            }else{
                return $this->countries->first();
            }
        }

    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? Storage::url($this->avatar_url) : null ;

    }
}
