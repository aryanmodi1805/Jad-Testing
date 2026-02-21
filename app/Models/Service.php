<?php

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\ServiceActiveScope;
use App\Models\Scopes\ServiceHasQuestionsScope;
use App\Models\Scopes\TenantScope;
use App\Traits\HasCountryScope;
use App\Traits\HasFullNameTranslation;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\BelongsToRelationship;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneOrManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

#[ScopedBy([ServiceHasQuestionsScope::class, ServiceActiveScope::class, TenantScope::class])]
class Service extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUuids;
    use HasTranslations;
    use HasFullNameTranslation;
    use HasCountryScope;

    public $translatable = ['name'];

    protected $fillable = [
        'name', 'slug', 'is_nationwide', 'is_remote', 'country_id', 'bark_id', 'image', 'active', 'category_id','keywords'
    ];

    protected $casts = [
        'keywords' => 'array',
        'is_nationwide' => 'boolean',
        'is_remote' => 'boolean',
        'active' => 'boolean',
    ];


    public function scopeActive(): Service
    {
        return $this->where('active', true);
    }

    /**
     * Scope a query to only include the most requested services.
     *
     * @param Builder $query
     * @param int $limit
     * @return Builder
     */
    public function scopeMostRequested(Builder $query, int $limit = 10): Builder
    {
        return $query->withCount('requests')
            ->orderBy('requests_count', 'desc')
            ->take($limit);
    }

    public function scopeHotNow(Builder $query, int $limit = 10): Builder
    {
        return $query->withCount(['requests' => function ($query) {
            $query->where('created_at', '>=', now()->subMonth());
        }])->orderBy('requests_count', 'desc')->take($limit);
    }

    public function scopeShowInHome(Builder $query): Builder
    {
        return $query->where('show_on_home_page', true);
    }


    public function scopeCustomerMostRequested(Builder $query, Customer $customer): Builder
    {
        return $query->withCount(['requests' => fn($query) => $query->where('customer_id', $customer->id)])
            ->orderBy('requests_count', 'desc');
    }


    function category(): BelongsTo
    {
        $parent = $this->subCategory?->parent();
        return $parent ? $parent : $this->subCategory();
    }


    function subCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * A description of the entire PHP function.
     *
     * @return HasMany
     */
    function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('sort');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function sellers(): BelongsToMany
    {
        return $this->belongsToMany(Seller::class, 'seller_services', 'service_id', 'seller_id');
    }

    public function sellerServices(): HasMany
    {
        return $this->hasMany(SellerService::class, 'service_id');
    }

    public function questionSuggestions(): HasMany
    {
        return $this->hasMany(QuestionSuggestion::class, 'service_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class, 'service_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class, 'service_id');
    }

    public function scopeNotAssignedToSeller($query, $sellerId)
    {
        return $query->whereDoesntHave('sellerServices', function ($query) use ($sellerId) {
            $query->where('seller_id', $sellerId);
        });
    }

    public function averageRating()
    {
        return $this->ratings()->avg('rating');
    }

    public function ratings()
    {
        return $this->hasManyThrough(Rating::class, Response::class, 'service_id', 'rateable_id')
            ->where('rateable_type', Response::class)->latest();
    }

    public function ratingsCount(): int
    {
        return $this->ratings()->count();
    }

    public function getImageUrl()
    {
        return $this->image ? Storage::url($this->image) : null;
    }

    public function scopeUnsubscribed(Builder $query, $type = "credit"): Builder
    {
        $ids = SubscriptionItem::whereHas('subscription', function ($query) {
            $query->where('seller_id', auth('seller')->user()->id)->active();
        })
            ->select('service_id as id')
            ->whereNotNull('service_id')
            ->where('type', $type)
            ->distinct();

        return $query->whereNotIn('id', $ids);
    }
}
