<?php

namespace App\Http\Resources;

use App\Models\Media;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var $this Project */
        return [
            "id" => $this->id,
            "title" => $this->title,
            "title_ar" => $this->getTranslation("title", "ar"),
            "title_en" => $this->getTranslation("title", "en"),
            "main_image" => $this->getFirstMedia('projects.main') ? mediaAppUrl($this->getFirstMedia('projects.main')->getUrl()) : null,
            "media" => MediaResource::Collection($this->getMedia('projects.more'))

        ];
    }
}
