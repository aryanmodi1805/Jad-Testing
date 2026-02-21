<?php

namespace App\Http\Resources;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if($this->payload != null){
            $payload = $this->payload;
            $payload['data']['currency'] = getCurrencySample();
        }
        /* @var $this Message*/
        return [
            "message" => $this->message,
            "sender_id" => $this->sender_id,
            "sender_type" => $this->sender_type,
            "attachments" =>  UrlResource::make($this->attachments),
            "payload" => $payload ?? null,
            "read_at" => $this->read_at,
            "created_at" => $this->created_at,

        ];
    }
}
