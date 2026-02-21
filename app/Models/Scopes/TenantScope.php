<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Skip tenant scope in local/testing environments or when no tenant is available
        if (app()->environment('local', 'testing', 'staging')) {
            return;
        }
        
        $tenant = getCurrentTenant();

        // Only apply scope if tenant exists
        if ($tenant?->id) {
            $builder->where($model->getTable() . '.country_id', $tenant->id);
        }
    }
}
