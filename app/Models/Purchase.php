<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use O21\LaravelWallet\Models\Transaction;

/**
 * @property mixed $refund_amount
 * @property mixed $request_refund
 */
class Purchase extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = ['id'];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function purchasable(): MorphTo
    {
        return $this->morphTo();
    }

    public function chargeable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getCurrency(): string
    {
        return strtoupper($this->is_form_wallet ? strtolower(__('wallet.credits')) : $this->currency);

    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function getCharge(): string
    {
        $chargeable = $this->chargeable;

        return
            ($chargeable instanceof Transaction) ? $chargeable->amount.' '.strtolower(__('wallet.credits')) :
                (($chargeable instanceof Subscription) ? __('subscriptions.subscription') : '*');

    }

    /**
     * @throws Exception
     */
    public function refund($refund_amount, $refund_reason): ?Purchase
    {
        if (! $this->allowRefund()) {
            throw new Exception('Refund not allowed');
        }
        if (! $this->checkEnoughCredits()) {
            throw new Exception('Not enough funds to credit');
        }
        $paymentDetail = PaymentDetail::find($this->payment_detail_id);

        if ($paymentDetail) {
            $newPaymentDetail = $paymentDetail->replicate()->fill([
                'refund_reason' => $refund_reason,
                'amount' => $refund_amount,
                'is_refund' => true,
                'status' => 0,
            ]);
            $newPaymentDetail->save();
        } else {
            throw new Exception('Payment detail not found');
            // Optionally, you can create a new PaymentDetail if that makes sense in your context
            //             $newPaymentDetail = PaymentDetail::create([
            //                 'refund_reason' => $refund_reason,
            //                 'amount' => $refund_amount,
            //                 'is_refund' => true,
            //                 'status' => 0,
            //             ]);
        }

        $copy = $this->replicate()->fill(
            [
                'refund_amount' => $refund_amount,
                'amount' => 0,
                'is_refund' => true,
                'request_refund' => true,
                'transaction_id' => null,
                'previous_tran_ref' => $this->transaction_id,
                'parent_id' => $this->id,
                'payment_detail_id' => $newPaymentDetail->id, 'status' => 0,
            ]
        );
        $copy->save();

        return $copy;
    }

    public function allowRefund(): bool
    {
        return ! ($this->is_form_wallet || $this->is_refund) && $this->checkEnoughCredits();
    }

    public function checkEnoughCredits(): bool
    {
        $chargeable = $this->chargeable;
        if ($chargeable instanceof Transaction || $chargeable instanceof Package) {
            if (! $this->payable->isEnoughFunds($chargeable->amount, $chargeable->currency)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function confirmRefund($status, $transaction_id, $data, $message = null): void
    {
        if (! $this->checkEnoughCredits()) {
            throw new Exception('Not enough funds to credit');
        }

        $this->status = $status;
        $this->amount = ($status == 1) ? (-1 * $this->refund_amount) : 0;
        $this->transaction_id = $transaction_id;
        $this->refund_response_message = $message;
        $this->save();
        $this->paymentDetails()->update(['payment_details' => json_encode($data), 'status' => $status]);
        $chargeable = $this->chargeable;
        // re-back chargeable

        if ($chargeable instanceof Subscription) {
            $chargeable->ended();
        } elseif ($chargeable instanceof Transaction || $chargeable instanceof Package) {
            if ($chargeable->hasStatus('success')) {

                $tx = tx($chargeable->amount)
                    ->currency($chargeable->currency)
                    ->processor('withdraw')
                    ->from($chargeable->to)
                    ->meta([
                        'data' => __('subscriptions.refund_action'),
                    ])->commit();
            }

        }

    }

    public function paymentDetails(): BelongsTo
    {
        return $this->belongsTo(PaymentDetail::class, 'payment_detail_id', 'id');

    }

    public function isDisableRefund(): bool
    {
        return $this->request_refund;
    }

    public function scopeSuccess($query, $country_id)
    {
        return $query->where('is_form_wallet', 0)->where('country_id', $country_id)->where('status', 1);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
