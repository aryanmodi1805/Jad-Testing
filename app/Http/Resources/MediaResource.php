<?php

namespace App\Http\Resources;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var $this Media */
        return [
            'id' => $this->id,
            'src' => mediaAppUrl($this->getUrl()),
            'thumb' => mediaAppUrl($this->getUrl('preview')),
            'width' => $this->width ?? null, // Replace with actual width if available
            'height' => $this->height ?? null, // Replace with actual height if available
            'alt' => $this->name,
        ];
    }
}
