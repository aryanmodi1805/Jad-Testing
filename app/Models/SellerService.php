<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class SellerService extends Model
{
    use HasFactory;

    protected $fillable = ['seller_id', 'service_id'];

    protected $primaryKey = 'id';
    protected $table = 'seller_services';


    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function sellerBlockedReports(): HasManyThrough
    {
        return $this->hasManyThrough(BlockReport::class, Seller::class , 'id', 'blocked_id','seller_id', 'id')->where('blocked_type',Seller::class);
    }
    public function sellerBlockerReports(): HasManyThrough
    {
        return $this->hasManyThrough(BlockReport::class, Seller::class , 'id', 'blocked_id','seller_id', 'id')->where('blocked_type',Customer::class);
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(SellerLocation::class, 'seller_service_locations', 'seller_service_id', 'seller_location_id')
            ->using(SellerServiceLocation::class);
    }

    public function sellerServiceLocations(): HasMany
    {
        return $this->hasMany(SellerServiceLocation::class, 'seller_service_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }



    public function scopeNationwide($query, $sellerId)
    {
        return $query->whereHas('locations', function ($query) use ($sellerId) {
            $query->where('seller_id', $sellerId)->where('is_nationwide', true);
        });
    }

    public function scopeWithSpecificLocations($query, $sellerId)
    {
        return $query->whereHas('locations', function ($query) use ($sellerId) {
            $query->where('seller_id', $sellerId)->where('is_nationwide', false);
        })->with(['locations' => function ($query) {
            $query->where('is_nationwide',false);
        }]);
    }

    public function scopeNotBlocked($query, $customerId)
    {
        return $query->doesntHave('sellerBlockedReports', callback: fn( $query) => $query->where('blocker_id', $customerId))
            ->doesntHave('sellerBlockerReports', callback: fn( $query) => $query->where('blocked_id', $customerId));
    }


}
