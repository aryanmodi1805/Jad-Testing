<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'recipient_ids',
        'recipient_type',
        'is_push',
        'is_database',
        'sent_at',
        'sent_by',
        'metadata'
    ];

    protected $casts = [
        'recipient_ids' => 'array',
        'metadata' => 'array',
        'is_push' => 'boolean',
        'is_database' => 'boolean',
        'sent_at' => 'datetime'
    ];

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function getRecipientsCountAttribute(): int
    {
        return match ($this->recipient_type) {
            'all' => Customer::count() + Seller::count(),
            'customers' => Customer::count(),
            'sellers' => Seller::count(),
            'specific' => count($this->recipient_ids ?? []),
            default => 0
        };
    }
}
