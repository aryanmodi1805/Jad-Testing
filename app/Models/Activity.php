<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    use HasFactory,HasUuids;

    protected $fillable= ['response_id', 'type', 'status', 'details'];

    public function response(): BelongsTo
    {
        return $this->belongsTo(Response::class);
    }
}
