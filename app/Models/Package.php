<?php

namespace App\Models;

use App\Interfaces\CanPayItem;
use App\Traits\HasTranslations;
use App\Traits\Wallet\Purchasable;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use MichaelRubel\Couponables\Traits\HasCoupons;

class Package extends Model implements CanPayItem
{
    use HasFactory;
    use SoftDeletes;

//    use HasUuids;
    use HasCoupons;
    use HasTranslations;

    use Purchasable;

    public $translatable = ['name', 'description'];
    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'description',
        'credits',
        'price',
        'apple_product_id',
        'is_ios_active',
        'ios_price',
        'ios_price_with_vat',

        'discount',
        'ex_VAT',
        'is_best_value',
        'is_active',
        'country_id',
        'currency_id',
    ];

    protected $casts = [
        'is_best_value' => 'boolean',
        'is_active' => 'boolean',
        'ex_VAT' => 'boolean',
        'is_ios_active' => 'boolean',
    ];

    public function getPriceWithVatAttribute()
    {
        $price = $this->attributes['price'];

        if ($this->ex_VAT) {
            return $price;
        }
        return  priceWithVat($price)  ;
    }
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class)->withDefault();

    }

    public function scopeTenant(Builder $query): Builder
    {
        return $query->where('country_id', auth(filament()->getAuthGuard())->user()->country_id);

    }

    public function getFinalPrice(): float
    {
        return $this->price_with_vat;
    }

    public function getWalletMeta(): array
    {
        return [
            'data' =>  __('wallet.pay') . " " . __('wallet.packages.single') . " (" . $this->name . ") [" . $this->credits . " credits ] ," . $this->description,
        ];
    }

    public function getPaymentTitle(): string
    {
        return $this->name . ' ' . __('wallet.packages.description');

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
}
