<?php

namespace App\Services\Payment;

use App\Services\Payment\Concerns\Payment;

class OnDelivered extends Payment
{

    public function createPayment()
    {
        return ['status' => 1, 'data' => array(), 'messages' => ["تمت عملية الدفع بنجاح"]];
    }

    public static function getProviderName(): string
    {
        return "manual";
    }
}
