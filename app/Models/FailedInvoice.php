<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FailedInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoiceable_id',
        'invoiceable_type',
        'transaction_id',
        'invoice_type',
        'amount',
        'currency',
        'error_message',
        'error_details',
        'request_payload',
        'resolved',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'error_details' => 'array',
        'request_payload' => 'array',
        'resolved' => 'boolean',
    ];

    /**
     * Get the user/seller who tried to create the invoice
     */
    public function invoiceable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for unresolved failures
     */
    public function scopeUnresolved($query)
    {
        return $query->where('resolved', false);
    }

    /**
     * Scope by invoice type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('invoice_type', $type);
    }
}
