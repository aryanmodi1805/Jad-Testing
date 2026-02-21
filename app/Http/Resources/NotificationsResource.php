<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->data['title'],
            'body' => $this->data['body'],
            'screen'=> $this->data['screen'] ?? null,
            'args' => $this->data['args'] ?? null,
//            'data' => $this->data,
            'read_at' => $this->read_at,
            'created_since' => $this->created_at->diffForHumans(),
            'created_at' => $this->created_at,
        ];
    }
}
