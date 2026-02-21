<?php

namespace App\Traits;
use Illuminate\Database\Eloquent\Builder;

trait HasCountryScope
{
    public function scopeCurrentCountry(Builder $query): Builder
    {
        return $query->where('country_id',getCountryId());
    }
}
