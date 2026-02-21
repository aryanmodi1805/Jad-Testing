<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerNotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = ["seller_id", "email_new_message", "email_invited", "email_new_request", "email_rated", "email_response_status_change", "push_new_message", "push_invited", "push_new_request", "push_rated", "push_response_status_change"];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }


}
