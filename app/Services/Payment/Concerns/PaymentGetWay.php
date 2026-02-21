<?php

namespace App\Services\Payment\Concerns;


use App\Models\PaymentDetail;
use App\Services\Payment\PaymentService;
use Filament\Facades\Filament;

trait PaymentGetWay
{
    public static function getKeysData(): array
    {
        return [];
    }

    public static function getAdditionalFields(): array
    {
        return [];
    }

    public static function getName(): string
    {
        return PaymentService::paymentsList[static::getProviderName()];
    }

    public static function needOtp(): bool
    {
        return false;
    }

    public static function needUserPassword(): bool
    {
        return false;
    }

    public static function needReceiptNumber(): bool
    {
        return false;
    }

    public static function getProviderName(): string
    {
        return "payment_type";
    }

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function updateDetailValidate($payment_detail_id, $status, $data)
    {
        PaymentDetail::update([
            'validate_details' => $data,
            'status' => $status,
        ])->where('id', $payment_detail_id);
    }

    public function updateDetailOtp($payment_detail_id, $status, $data)
    {
        PaymentDetail::update([
            'otp_details' => $data,
            'status' => $status,
        ])->where('id', $payment_detail_id);
    }

    public function updateDetailPayment($payment_detail_id, $status, $data)
    {
       return PaymentDetail::find($payment_detail_id)->update([
            'payment_details' => $data,
            'status' => $status,
        ]);
            //->where('id', $payment_detail_id);
    }

    public function newDetailPayment($paymentMethodId, $customerId,$country_id,$user_id,$amount)
    {
        return PaymentDetail::create([
            "payment_method_id" => $paymentMethodId,
            'purchase_id' => $customerId,
            'country_id' =>   $country_id ,
            'seller_id' => $user_id  ,
            'amount' => $amount  ,
        ])->id;
    }

}
