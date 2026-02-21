<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SellerLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        "seller_id", "name", "latitude", "longitude", "location_range", "is_nationwide", "country_id", "location_name"];

    protected $appends = [
        'location',
    ];


    public function services(): BelongsToMany
    {
        return $this->belongsToMany(SellerService::class, 'seller_service_locations', 'seller_location_id', 'seller_service_id')
            ->using(SellerServiceLocation::class)
            ->withTimestamps();
    }
    public function sellerServiceLocations(): HasMany
    {
        return $this->hasMany(SellerServiceLocation::class, 'seller_location_id');
    }
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Scope a query to only include locations within a certain distance from a given point.
     *
     * @param Builder $query
     * @param float $latitude
     * @param float $longitude
     * @param int $radius The radius in kilometers.
     * @return Builder
     */
    public function scopeWithinDistance(Builder $query, float $latitude, float $longitude, int $radius = 50): Builder
    {
        $haversine = "(6371 * acos(cos(radians($latitude))
        * cos(radians(latitude))
        * cos(radians(longitude) - radians($longitude))
        + sin(radians($latitude))
        * sin(radians(latitude))))";
        return $query->havingRaw("$haversine < ?", [$radius]);
    }


    public function getLocationAttribute(): array
    {
        return [
            "lat" => (float)$this->latitude,
            "lng" => (float)$this->longitude,
        ];
    }

    /**
     * Takes a Google style Point array of 'lat' and 'lng' values and assigns them to the
     * 'latitude' and 'longitude' attributes on this model.
     *
     * Used by the Filament Google Maps package.
     *
     * Requires the 'location' attribute be included in this model's $fillable array.
     *
     * @param ?array $location
     * @return void
     */
    public function setLocationAttribute(?array $location): void
    {
        if (is_array($location))
        {
            $this->attributes['latitude'] = $location['lat'];
            $this->attributes['longitude'] = $location['lng'];
            unset($this->attributes['location']);
        }
    }
    /**
     * Get the lat and lng attribute/field names used on this table
     *
     * Used by the Filament Google Maps package.
     *
     * @return string[]
     */
    public static function getLatLngAttributes(): array
    {
        return [
            'lat' => 'latitude',
            'lng' => 'longitude',
        ];
    }
    /**
     * Get the name of the computed location attribute
     *
     * Used by the Filament Google Maps package.
     *
     * @return string
     */
    public static function getComputedLocation(): string
    {
        return 'location';
    }
}
