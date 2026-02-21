<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlockReason extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasTranslations;

    protected $fillable = ["name"] ;

    protected $translatable = ["name"] ;

    public function blockReports(): HasMany
    {
        return $this->hasMany(BlockReport::class, 'block_reason_id');
    }
}
