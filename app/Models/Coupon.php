<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends \MichaelRubel\Couponables\Models\Coupon
{
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
