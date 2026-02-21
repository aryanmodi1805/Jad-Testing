<?php

namespace App\Models;

use App\Traits\HasFullNameTranslation;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\HtmlString;

/**
 * @method static where(string $string, string $getProviderName)
 */
class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes;
    use HasTranslations;
    use HasFullNameTranslation;

    public $translatable = ['name'];
    protected $fillable = [
        'name',
        'payment_type_id',
        'type',
        'active',
        'country_id',
        'additional_fields',
        'details',
        'logo',
    ];
    protected $casts = [
        'details' => 'json',
        'name' => 'json',
        'additional_fields' => 'json'
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function getNameHtmlAttribute(): HtmlString
    {
        return new HtmlString('<div class="  flex items-center  justify-self-start hover:items-stretch">

                                          <div class="justify-self-start">
                                          <img class="h-[40px]" src="' . $this->svg_logo . '" alt="'.$this->name.'"/>

                                             </div>
                                    </div>
                                    ');
    }

    public function getSvgLogoAttribute(): string
    {
        return  $this->logo ? url('/storage/' . $this->logo) :     url('/payment-logos/svg/' . $this->type . '.svg');
    }
}
