<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static create(array $array)
 */
class PaymentDetail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts=[
        'payment_details'=>'array',
        'otp_details'=>'array',
        'validate_details'=>'array',

    ];
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
public function method(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id', 'id');
    }

}
