<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static create(array $logsData)
 */
class SmsLogs extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $fillable = [
        'user_id',
        'MSG_ID',
        'phone',
        'sms_text',
        'status',
        'error_Code',
        'error_message',
        'response',
    ];
    protected $casts = [
        'response' => 'array',
    ];

    public function seeker(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
}
