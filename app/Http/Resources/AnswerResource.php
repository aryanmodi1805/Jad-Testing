<?php

namespace App\Http\Resources;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnswerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var $this Answer*/
        return [
            "id" => $this->id,
            "label" => $this->label,
            "type" => $this->type?->value,
            "has_another_input" => $this->has_another_input,
        ];
    }
}
