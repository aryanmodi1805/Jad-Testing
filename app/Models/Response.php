<?php

namespace App\Models;

use App\Enums\ResponseStatus;
use App\Observers\ResponseObserver;
use App\Traits\Rateable;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use OwenIt\Auditing\Contracts\Auditable;

#[ObservedBy([ResponseObserver::class])]
class Response extends Model implements Auditable
{
    use HasFactory,HasUuids,Rateable;

    use \OwenIt\Auditing\Auditable;


    protected $casts = [
        'status' => ResponseStatus::class,
    ];
    protected $fillable = ['request_id', 'seller_id', 'status', 'notes', 'is_approved', 'service_id'];

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'response_id');
    }

    public function estimate(): HasOne
    {
        return $this->hasOne(Estimate::class, 'response_id')
            ->with('estimateBase');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function customer(): HasOneThrough
    {
        return $this->hasOneThrough(Customer::class, Request::class, 'id', 'id', 'request_id', 'customer_id');
    }

    public function otherParty(){
        return $this->seller_id == Filament::auth()->id() ? $this->customer : $this->seller;
    }
    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class);
    }

    public function requestResponses()
    {
        return $this->hasManyThrough(Response::class, Request::class, 'id', 'request_id', 'request_id')->where('responses.status','!=', ResponseStatus::Invited);

    }

    public function latestActivity(): HasOne
    {
        return $this->hasOne(Activity::class)->latestOfMany();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->latest();
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function isBlocked(?Model $currentUser, ?Model $otherUser)
    {
        return BlockReport::where('blocker_id', $currentUser->id)
            ->where('blocker_type', get_class($currentUser))
            ->where('blocked_id', $otherUser->id)
            ->where('blocked_type', get_class($otherUser))
            ->exists();
    }

    public function canPayCash(): bool
    {
        $estimate = $this->estimate;
        if (!$estimate) {
            return false;
        }

        $commissionRate = 0.30; // 30%
        $commissionAmount = $estimate->amount * $commissionRate;
        $seller = $this->seller;

        if (!$seller) {
            return false;
        }

        $currentBalance = $seller->balance() ? (float)(string)$seller->balance()->value : 0.0;

        return $currentBalance >= $commissionAmount;
    }
}
