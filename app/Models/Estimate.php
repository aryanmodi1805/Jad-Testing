<?php

namespace App\Models;

use App\Observers\EstimateObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

#[ObservedBy(EstimateObserver::class)]
class Estimate extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'response_id',
        'amount',
        'estimate_base_id',
        'details',
    ];

    /**
     * Safely return amount with currency and estimate base name.
     * Prevents 500 errors when estimateBase is null.
     */
    protected function amountPerBase(): Attribute
    {
        return Attribute::make(
            get: fn () => sprintf(
                '%s %s %s',
                $this->amount,
                getCurrencySample(),
                $this->estimateBase?->name ?? '—'
            )
        );
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(Response::class);
    }

    public function estimateBase(): BelongsTo
    {
        return $this->belongsTo(EstimateBase::class, 'estimate_base_id');
    }

    public function message(): MorphOne
    {
        return $this->morphOne(Message::class, 'type');
    }
}
