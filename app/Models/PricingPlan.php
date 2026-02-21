<?php

namespace App\Models;

use App\Enums\Wallet\CreditType;
use App\Enums\Wallet\PremiumType;
use App\Interfaces\CanPayItem;
use App\Traits\HasTranslations;
use App\Traits\Wallet\Purchasable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PricingPlan extends Model implements CanPayItem
{
    use HasFactory, softDeletes, HasTranslations, Purchasable,SoftDeletes;

    public $translatable = ['name', 'description', 'features', 'tag'];
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'description',
        'billing_cycles',
        'month_price',
        'apple_product_id',
        'is_ios_active',
        'ios_price',
        'ios_price_with_vat',
        'year_price',
        'features',
        'price',
        'discount',
        'ex_VAT',
        'is_best_value',
        'is_active',
        'country_id',
        'tag',
        'currency_id',
        'is_premium',
        'is_unlimited',


        'credit_limit',
        'trial_days',
        'is_trial',
        'is_in_credit',
        'premium_type',
        'credit_type',
        'premium_items_limit',
        'credit_items_limit',
        'bg_color',
        'text_color',

    ];
    protected $casts = [
        'features' => 'json',

        'is_premium' => 'boolean',
        'is_unlimited' => 'boolean',
        'is_trial' => 'boolean',
        'is_best_value' => 'boolean',
        'is_active' => 'boolean',
        'is_ios_active' => 'boolean',

        'premium_type' => PremiumType::class,
        'is_in_credit' => 'boolean',
        'credit_type' => CreditType::class,
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');

    }

    public function scopeTenant(Builder $query): Builder
    {
        return $query->where('country_id', auth()->user()->country_id);

    }
    public function getPriceWithVatAttribute()
    {
        $price = $this->attributes['month_price'];

        if ($this->ex_VAT) {
            return $price;
        }
        return  priceWithVat($price)  ;
    }
    public function getFinalPrice(): float
    {
        $price = $this->attributes['month_price'];

        if ($this->ex_VAT) {
            return $price;
        }
        return priceWithVat($price) ?? $this->price_with_vat?? 0;
    }

    public function getIosFinalPrice(): float
    {
        if ($this->ios_price_with_vat) {
            return (float) $this->ios_price_with_vat;
        }
        
        if ($this->ios_price) {
            return (float) $this->ios_price;
        }
        
        // Fallback to regular price if iOS price is not set
        return $this->getFinalPrice();
    }

    public function getWalletMeta(): array
    {
        return ['data' => __('wallet.pay') . " " . $this->name . '(' . $this->description . ')', 'description' => $this->description, ''];
    }

    public function getPaymentTitle(): string
    {
        return __('wallet.plans.single') . ' ' . $this->name;
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'price_plan_id','id');

    }
}
