<?php

namespace App\Traits\Wallet;

use App\Models\Purchase;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Purchasable
{


    public function purchases(): MorphMany
    {
        return $this->morphMany(Purchase::class, 'purchasable');
    }

    /**
     * Have I been previously purchased by given payable or buyer
     * @param $payable
     * @return bool
     */
    public function is_purchased($payable): bool
    {
        return $this->purchases()->where('payable_id', $payable->id)->where('status',1)->exists();

    }

}
