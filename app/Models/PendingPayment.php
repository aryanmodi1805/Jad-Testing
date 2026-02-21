<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_type', // 'seller' or 'customer'
        'charge_id',
        'response_id',
        'payment_type',
        'amount',
        'currency',
        'status',
        'verification_attempts',
        'last_verified_at',
        'expires_at',
        'metadata',
        'failure_reason',
    ];

    protected $casts = [
        'metadata' => 'array',
        'amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'last_verified_at' => 'datetime',
    ];

    /**
     * Get the user (seller or customer) that owns the pending payment
     */
    public function user(): BelongsTo
    {
        $modelClass = $this->user_type === 'customer' ? Customer::class : Seller::class;
        return $this->belongsTo($modelClass, 'user_id');
    }
    
    /**
     * Get the seller if user_type is 'seller'
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'user_id');
    }
    
    /**
     * Get the customer if user_type is 'customer'
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }

    /**
     * Get the response associated with this payment (for service payments)
     */
    public function response(): BelongsTo
    {
        return $this->belongsTo(Response::class, 'response_id');
    }

    /**
     * Check if the pending payment has expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }

    /**
     * Check if the pending payment is still active
     */
    public function isActive(): bool
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    /**
     * Get seconds remaining until expiration
     */
    public function secondsRemaining(): int
    {
        if ($this->isExpired()) {
            return 0;
        }
        
        return now()->diffInSeconds($this->expires_at);
    }
}
