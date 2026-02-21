<?php

namespace App\Models;

use App\Enums\QuestionType;
use App\Traits\HasFullNameTranslation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Question extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUuids;
    use \App\Traits\HasTranslations;
    use HasFullNameTranslation;

    public $translatable = ['label'];

    protected $fillable = ['label', 'type', 'image', 'service_id', 'sort', 'dependent_question_id', 'dependent_answer_id', 'is_required', 'is_custom', 'val'];
    protected $casts = [
        'type' => QuestionType::class,
        'is_required' => 'boolean',
        'is_custom' => 'boolean',
    ];

    public function getNameColumn() : string {
        return "label";
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

     /**
     * Retrieve the answers associated with this instance.
     *
     * @return mixed
     */
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
    public function dependentQuestion(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Question::class, 'dependent_question_id');
    }

    public function dependentQuestions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Question::class, 'dependent_question_id');
    }

    public function dependentAnswer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Answer::class, 'dependent_answer_id');
    }

    public function questionSuggestions(): HasMany
    {
        return $this->hasMany(QuestionSuggestion::class, 'question_id');
    }



}
