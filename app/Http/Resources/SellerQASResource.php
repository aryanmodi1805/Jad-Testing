<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerQASResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "question" => $this->question,
            "question_ar"=> $this->getTranslation("question", "ar"),
            "question_en"=> $this->getTranslation("question", "en"),
            "answer" => $this->answer,
            "answer_ar"=> $this->getTranslation("answer", "ar"),
            "answer_en"=> $this->getTranslation("answer", "en"),
        ];
    }
}
