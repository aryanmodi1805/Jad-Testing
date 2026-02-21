<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerNotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = ["customer_id", "email_new_message", "email_new_estimate", "email_new_response", "email_accepted_invitation", "email_request_status_change", "push_new_message", "push_new_estimate", "push_new_response", "push_accepted_invitation", "push_request_status_change"];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }


}
