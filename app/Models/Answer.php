<?php

namespace App\Models;

use App\Casts\AnswerTypeCast;
use App\Enums\AnswerType;
use App\Enums\QuestionType;
use App\Traits\HasFullNameTranslation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Answer extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUuids;
    use \App\Traits\HasTranslations;
    use HasFullNameTranslation;

    public $translatable = ['label'];

    protected $fillable = ['label', 'val', 'type', 'image', 'rule', 'question_id', 'sort', 'is_custom', 'has_another_input'];

    protected $casts = [
        'type' => AnswerTypeCast::class,
        'is_custom' => 'boolean',
        'has_another_input' => 'boolean',
    ];
    public function getNameColumn() : string {
        return "label";
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function customerAnswers(): HasMany
    {
        return $this->hasMany(CustomerAnswer::class, 'answer_id');
    }




}
