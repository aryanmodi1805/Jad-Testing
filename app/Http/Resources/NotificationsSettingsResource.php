<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationsSettingsResource extends JsonResource
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
            'email_new_message' => $this->email_new_message,
            'email_new_estimate' => $this->email_new_estimate,
            'email_new_response' => $this->email_new_response,
            'email_accepted_invitation' => $this->email_accepted_invitation,
            'email_request_status_change' => $this->email_request_status_change,
            'push_new_message' => $this->push_new_message,
            'push_new_estimate' => $this->push_new_estimate,
            'push_new_response' => $this->push_new_response,
            'push_accepted_invitation' => $this->push_accepted_invitation,
            'push_request_status_change' => $this->push_request_status_change,
        ];
    }
}
