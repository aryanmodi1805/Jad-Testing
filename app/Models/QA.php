<?php

namespace App\Models;

use App\Traits\HasFullNameTranslation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class QA extends Model
{
    use HasFactory;
    use \App\Traits\HasTranslations;
    use SoftDeletes;
    use HasFullNameTranslation;

    protected $table = 'q_a_s';

    public $translatable = ['name'];

    protected $fillable = [
        'name', 'active', 'order', 'deleted_at',
    ];

    public function sellerQAs(): HasMany
    {
        return $this->hasMany(SellerQA::class, 'q_a_s_id');
    }
}
