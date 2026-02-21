<?php

namespace App\Models;

use App\Traits\HasFullNameTranslation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class City extends Model
{
    use HasFactory;
    use \App\Traits\HasTranslations;
    use HasFullNameTranslation;

    public $translatable = ['name'];

  /*   protected $casts = [
        'name' => 'array',
    ]; */

    protected $fillable = ['name', 'country_id', 'active'];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function sellerLocations(): HasMany
    {
        return $this->hasMany(SellerLocation::class, 'city_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class, 'city_id');
    }
}
