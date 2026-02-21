<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SellerServiceLocation extends Pivot
{
    protected $table = 'seller_service_locations';
    protected $fillable = ['seller_service_id', 'seller_location_id', "location_range", "is_nationwide"];
    public function sellerLocation(): BelongsTo
    {
        return $this->belongsTo(SellerLocation::class);
    }

    public function service(): HasOneThrough
    {
        return $this->hasOneThrough(Service::class, SellerService::class, 'id', 'id', 'seller_service_id', 'service_id');
    }

    public function sellerService(): BelongsTo
    {
        return $this->belongsTo(SellerService::class);
    }


}
