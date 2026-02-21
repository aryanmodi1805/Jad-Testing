<?php

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\OrderBySort;
use App\Traits\HasFullNameTranslation;
use App\Traits\HasTranslations;
use App\Traits\Rateable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use MichaelRubel\Couponables\Models\Coupon;

#[ScopedBy([ActiveScope::class, OrderBySort::class])]
class Country extends Model
{
    use HasFactory;
    use HasTranslations;
    use HasFullNameTranslation;
    use Rateable;

    public $translatable = ['name'];
    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'active',
        'code',
        'currency_id',
        'location',
        'slug',
        'credit_price',
        'vat_percentage',
    ];

    protected $casts = [
        'location' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'country_id');
    }


    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_countries');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'country_id');
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class, 'country_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);

    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class, 'country_id');
    }

    public function sellers(): HasMany
    {
        return $this->hasMany(Seller::class, 'country_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'country_id');
    }

    public function pricingPlans(): HasMany
    {
        return $this->hasMany(PricingPlan::class, 'country_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class, 'country_id');
    }

    public function responses(): HasManyThrough
    {
        return $this->hasManyThrough(Response::class, Request::class);
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class, 'country_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'country_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'country_id');

    }

}
