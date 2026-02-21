<?php

namespace App\Http\Resources;

use App\Models\Customer;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var $this Customer */
        return [
            "id" => $this->id,
            "name" => $this->name,
            "phone" => $this->phone,
            "email" => $this->email,
            "avatar" => appUrl($this->avatar_url),
            'is_phone_verified' => $this->isPhoneVerified,
            'regular_customer' => ((int) ($this->requests_count ?? 0)) > app(GeneralSettings::class)->regular_customer_badge,
            "ratings_avg" => $this->rate,
            "ratings_count" => $this->rate_count,

        ];
    }
}
