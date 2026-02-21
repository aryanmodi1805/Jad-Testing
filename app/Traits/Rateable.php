<?php

namespace App\Traits;

use App\Models\Rating;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Rateable
{
    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable')->latest();
    }

    public function averageRating()
    {
        return $this->ratings()->where('approved',true)->avg('rating');
    }

    public function ratingsCount(): int
    {
        return $this->ratings()->count();
    }
}
