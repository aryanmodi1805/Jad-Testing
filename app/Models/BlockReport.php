<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlockReport extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ["blocked_id", "blocked_type", "block_reason_id", "details" , "reference_id", "reference_type", "blocker_id", "blocker_type"];


    public function blockReason(): BelongsTo
    {
        return $this->belongsTo(BlockReason::class);
    }

    public function blocked()
    {
        return $this->morphTo();
    }

    public function blocker()
    {
        return $this->morphTo();
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
