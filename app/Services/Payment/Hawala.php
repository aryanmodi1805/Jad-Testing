<?php

namespace App\Services\Payment;

use App\Services\Payment\Concerns\KeysData;
use App\Services\Payment\Concerns\Payment;

class Hawala extends Payment
{

    public function createPayment($payment_purchase_id,$receiptId)
    {
        if ( empty($receiptId))
            return ['status' => 0, 'data' => array(), 'messages' => ["رقم الحوالة غير موجود يرجى تحديث التطبيق"]];
        return ['status' => 1, 'data' => array(
            "payment_purchase_id" => $payment_purchase_id, // $result["ref_id"],
            "details" => $receiptId,
        ), 'messages' => ["نجحت عملية الدفع"]];

    }

    public static function needReceiptNumber():bool{
        return true;
    }

    public static function getProviderName(): string
    {
        return "hawala";
    }

    public static function getAdditionalFields(): array
    {
        return [
            KeysData::ReceiverName => '',
            KeysData::ReceiverMobile => '',

        ];
    }
}
