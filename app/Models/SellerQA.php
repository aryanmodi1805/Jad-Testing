<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasTranslations;
use App\Traits\HasFullNameTranslation;

class SellerQA extends Model
{
    use HasFactory,HasTranslations,HasFullNameTranslation;

    protected $table = 'seller_q_a_s';

    protected $fillable = [
        'seller_id', 'answer', 'question',
    ];

    public $translatable = ['answer', 'question'];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function qAS(): BelongsTo
    {
        return $this->belongsTo(QA::class);
    }
}
