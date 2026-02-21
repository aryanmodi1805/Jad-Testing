<?php

namespace App\Models;

use App\Enums\ResponseStatus;
use App\Observers\SellerObserver;
use App\Traits\Auth\HasSMSNotification;
use App\Traits\HasFullNameTranslation;
use App\Traits\Wallet\CanPay;
use App\Traits\Wallet\HasSubscription;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use LaraZeus\Sky\Models\Post;
use MichaelRubel\Couponables\Traits\HasCoupons;
use O21\LaravelWallet\Contracts\Payable;
use O21\LaravelWallet\Models\Concerns\HasBalance;
use Spatie\MediaLibrary\Conversions\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Traits\HasTranslations;

#[ObservedBy(SellerObserver::class)]
class Seller extends Authenticate implements Payable, HasAvatar, HasMedia, FilamentUser, HasTenants
{
    use HasApiTokens, HasFactory;
    use TwoFactorAuthenticatable;
    use HasBalance;
    use HasCoupons;
    use CanPay;
    use InteractsWithMedia;
    use HasSubscription;
    use HasTranslations;
    use softDeletes;
    use HasSMSNotification;
    use Notifiable;

    /**
     * The attributes that are mass assignable.n
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'email', 'email_verified_at', 'password', 'remember_token', 'deleted_at', 'phone', 'country_id',
        'company_description', 'years_in_business', 'company_size_id', 'location', 'website', 'company_name',
        'avatar_url', 'blocked', 'phone_verified_at','cover_image', 'average_response', 'tokens' ,'locale', "rate", "rate_count", 'customer_id',
        'identification_document_url', 'identification_document_status', 'identification_document_rejection_reason', 'identification_document_verified_at'
    ];

    public $translatable = ['company_description', 'company_name'];
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
        'phone_verified_at' => 'datetime',
        'identification_document_verified_at' => 'datetime',
        'password' => 'hashed',
        'tokens' => 'array',
    ];

    public function updateAvgResponse(): void
    {
        $avg = DB::table('audits')
            ->leftJoin('responses', 'responses.id', '=', 'audits.auditable_id')
            ->leftJoin('requests', 'requests.id', '=', 'responses.request_id')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, requests.created_at, responses.created_at))')
            ->where('user_type', Seller::class)
            ->where('user_id', $this->id)
            ->where('auditable_type', Response::class)
            ->where(function ($query) {
                $query->where('event', 'created')
                    ->orWhere(function ($query) {
                        $query->where('event', 'updated')
                            ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(old_values, \'$.status\')) = \'0\'')
                            ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(new_values, \'$.status\')) = \'1\'');
                    });
            })
            ->value('AVG(TIMESTAMPDIFF(MINUTE, requests.created_at, responses.created_at))');
        
        // Handle null case - set to 0 if no data available
        $this->update(['average_response' => $avg ?? 0]);
    }

    public function profileCompletionRate()
    {
        $fields = [
            'company_description',
            'company_size_id',
            'website',
            'company_name',
            'years_in_business',
            'avatar_url',
            'cover_image',
        ];

        $completed = collect($fields)->filter(fn($field) => !empty($this->$field))->count();

        $relations = [
            'sellerLocations',
            'sellerServices',
            'media',
            'socialMedia',
            'projects',
            'qas',
            'sellerProfileServices',
        ];

        $completed += collect($relations)->filter(fn($relation) => $this->$relation()->exists())->count();

        $total = count($fields) + count($relations);

        return round(($completed / $total) * 100);
    }

    public function updateRating(): void
    {
        $this->update([
            'rate' => $this->averageRating(),
            'rate_count' => $this->ratingsCount()
        ]);
    }

    public function associatedAccount(): BelongsTo
    {
        return $this->belongsTo(Customer::class,'customer_id');
    }
    public function canAccessPanel(Panel $panel): bool
    {
        return !$this->blocked;
    }

    public function location(): HasMany
    {
        return $this->hasMany(SellerLocation::class, 'seller_id');
    }

    public function serviceLocation(): HasManyThrough
    {
        return $this->hasManyThrough(SellerServiceLocation::class, SellerService::class, 'seller_id', 'seller_service_id');
    }


    public function blockReport(): MorphMany
    {
        return $this->morphMany(BlockReport::class, 'blocked');
    }

    public function blocks(): MorphMany
    {
        return $this->morphMany(BlockReport::class, 'blocker');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'seller_services')
            ->withPivot(['id', 'seller_id', 'service_id'])
            ->withTimestamps();
    }

    public function sellerServices(): HasMany
    {
        return $this->hasMany(SellerService::class, 'seller_id');
    }

    public function sellerLocations(): HasMany
    {
        return $this->hasMany(SellerLocation::class, 'seller_id');
    }

    public function requests(): BelongsToMany
    {
        return $this->belongsToMany(Request::class, 'responses')
            ->withPivot('status', 'notes')
            ->withTimestamps();
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }


    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? Storage::url($this->avatar_url) : null;
    }

    public function getCoverImageUrl() : ?string
    {

        return $this->cover_image ? Storage::url($this->cover_image) : null;
    }

    public function socialMedia(): HasMany
    {
        return $this->hasMany(SellerSocialMedia::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
    public function qas(): HasMany
    {
        return $this->hasMany(SellerQA::class);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->width(336)
            ->height(252)
            ->nonQueued();
    }

    public function sellerQAs(): HasMany
    {
        return $this->hasMany(SellerQA::class, 'seller_id');
    }

    public function companySize(): BelongsTo
    {
        return $this->belongsTo(CompanySize::class);
    }

    public function sellerProfileServices(): HasMany
    {
        return $this->hasMany(SellerProfileService::class, 'seller_id');
    }

    public function questionSuggestions(): HasMany
    {
        return $this->hasMany(QuestionSuggestion::class, 'seller_id');
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

    public function ratings(): HasManyThrough
    {
        return $this->hasManyThrough(Rating::class, Response::class, 'seller_id', 'rateable_id')
            ->where('rateable_type', Response::class)->latest();
    }

    public function ratingsCount(): int
    {
        return $this->ratings()->count() ?? 0;
    }


    public function getBio()
    {
        return $this->company_description;
    }

    public function hiredProjectsCount(): int
    {
        return $this->responses()->where('status', ResponseStatus::Hired)->count();

    }

    public function isInvited($request_id) : bool
    {
        return $this->responses()->where('status',ResponseStatus::Invited)->where('request_id',$request_id)->exists();
    }

    public function scopeTenant(Builder $query , $country_id = null): Builder
    {
        return $query->where('country_id', $country_id ??  getCountryId());
    }

    public function scopeNotBlockedCustomer(Builder $query, $customer): Builder
    {
        $sellers  = BlockReport::selectRaw("
                CASE
                    WHEN blocked_type = ? THEN blocker_id
                    WHEN blocker_type = ? THEN blocked_id
                END as seller_id", [Customer::class, Customer::class])
            ->where(fn($query) => $query
                ->where('blocked_id', $customer->id)
                ->where('blocked_type', Customer::class))
            ->orWhere(fn($query) => $query
                ->where('blocker_id', $customer->id)
                ->where('blocker_type', Customer::class))
            ->toRawSql();

        return $query->whereRaw($query->qualifyColumn('id') . " not in ($sellers)");
    }
    public function scopeCanServeRequest(Builder $query, $request): Builder
    {
        return $query
            ->notBlockedCustomer($request?->customer)
            ->joinSub(
            SellerService::query()
                ->selectRaw("seller_services.seller_id, MIN(subquery.distance) as distance")
                ->joinSub(
                    SellerService::query()
                        ->selectRaw("seller_services.seller_id as seller_id, request_distance({$request->latitude}, {$request->longitude}, seller_locations.latitude, seller_locations.longitude) as distance, seller_service_locations.location_range, seller_service_locations.is_nationwide")
                        ->joinRelation('sellerServiceLocations', joinType: 'leftJoin')
                        ->leftJoin('seller_locations', 'seller_service_locations.seller_location_id', '=', 'seller_locations.id')
                        ->where('seller_services.service_id', $request->service_id)
                        ->whereNotNull('seller_locations.latitude')
                        ->whereNotNull('seller_locations.longitude')
                        ->havingRaw('(is_nationwide = 1 OR distance <= location_range)'),
                    'subquery', 'seller_services.seller_id', '=', 'subquery.seller_id'
                )
                ->groupBy('seller_services.seller_id')
                ->orderBy('distance', 'asc')
            ,
            'location_distance', 'location_distance.seller_id', '=', 'sellers.id' );
    }

    public function notificationSettings(): HasOne
    {
        return $this->hasOne(SellerNotificationSetting::class, 'seller_id');
    }

    static public function getMatchingSeller(Request $request): Builder
    {
        return getPremiumSellers($request->service_id)
            ->selectRaw("sellers.* , location_distance.distance as distance")
            ->canServeRequest($request)
            ->whereDoesntHave('responses', callback: fn($query) => $query->where('request_id', $request->id))
            ->orderBy('location_distance.distance', 'asc');
    }



}
