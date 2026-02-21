<?php

namespace App\Models;

use App\Enums\RateableType;
use App\Enums\RaterType;
use App\Models\Scopes\ApprovedScope;
use App\Observers\RatingObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
#[ObservedBy(RatingObserver::class)]
#[ScopedBy([ApprovedScope::class])]
class Rating extends Model
{
    use HasFactory ,SoftDeletes;

    protected $fillable = ['rating', 'review', 'rateable_id', 'rateable_type', 'rater_id', 'rater_type', 'approved', 'show_on_homepage', 'language'];

    public function rateable(): MorphTo
    {
        return $this->morphTo();
    }

    public function rater(): MorphTo
    {
        return $this->morphTo();
    }

    public function getRateableTypeLabelAttribute(): string
    {
        $rateableType = RateableType::from($this->rateable_type);
        return $rateableType->getLabel();
    }

    public function getRaterTypeLabelAttribute(): string
    {
        $raterType = RaterType::from($this->rater_type);
        return $raterType->getLabel();
    }

    public function customer() : HasOneThrough
    {
        return $this->hasOneThrough(Customer::class, Request::class, 'id', 'id', 'rateable_id', 'customer_id');

    }

    public function seller() : HasOneThrough
    {
        return $this->hasOneThrough(Seller::class, Response::class, 'id', 'id', 'rateable_id', 'seller_id');

    }
}
