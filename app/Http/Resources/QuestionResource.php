<?php

namespace App\Http\Resources;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var $this Question*/
        return [
            "id" => $this->id,
            "label" => $this->label,
            "type" => $this->type?->value,
            "is_required" => $this->is_required,
            "dependent_question_id" => $this->dependent_question_id,
            "dependent_answer_id" => $this->dependent_answer_id,
            "answers" => AnswerResource::collection($this->answers),

        ];
    }
}
