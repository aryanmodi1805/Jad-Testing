<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionSuggestion extends Model
{

    use HasFactory,HasUuids;

    protected $fillable = ['service_id', 'question_id', 'type', 'name', 'question_type','seller_id'];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function answerSuggestions(): HasMany
    {
        return $this->hasMany(AnswerSuggestion::class, 'question_suggestion_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }
}
