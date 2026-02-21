<?php

namespace App\Services\Payment;

use App\Services\Payment\Concerns\KeysData;
use App\Services\Payment\Concerns\Payment;
use Illuminate\Support\Facades\Http;

class Floosak extends Payment
{

    const url = 'https://cmc.qchosts.com/api/merchant/v1/';

    public static function getProviderName(): string
    {
        return 'floosak';
    }


    public static function getKeysData(): array
    {
        return [
            KeysData::Key => '',
            KeysData::PublicKey => '',
            KeysData::RequestId => '',
        ];
    }

    public static function getName(): string
    {
        return PaymentService::paymentsList[static::getProviderName()];
    }

    public static function needOtp(): bool
    {
        return true;
    }

    public static function needReceiptNumber(): bool
    {
        return false;
    }

    public function createPayment($paymentMethodId, $customerPhone, $customerId, $amount)
    {
        $header = array(
            "Authorization:Bearer " . $this->data[KeysData::Key],
            "Content-Type: application/json",
            "x-channel: merchant",
            "Accept: application/json"
        );
        $url = self::url . '/api/v1/merchant/p2mcl';
        $params = [];
        foreach ($this->data as $key => $value) {
            if ($key != 'url')
                $params[$key] = $value['value'];
        }
        $payment_detail_id = $this->newDetailPayment($paymentMethodId,$customerId);
        $params['ref_id'] = $payment_detail_id;
        $params["source_wallet_id"] = $this->data[KeysData::PublicKey];
        $params["request_id"] = $this->data[KeysData::RequestId];
        $params["purpose"] = "الدفع عبر " . env("APP_NAME", "") . " برقم " . $payment_detail_id;
        $params["target_phone"] = str_replace("+", "", $customerPhone);
        $params["amount"] = $amount;

        $result = Http::withHeaders($header)
            ->withoutVerifying()
            ->post($url,$params);
        if (isset($result["is_success"]) && $result["is_success"]) {
            $this->updateDetailPayment($payment_detail_id,2,$result);
            return ['status' => 2, 'data' => array(
                "payment_detail_id" =>  $params['ref_id'],
                "ref_id" => $result["data"]['id'],
            ), 'messages' => ["تم ارسال كود الدفع الى رقم تلفونك يرجى ادخال الكود"]];
        }else
            $this->updateDetailPayment($payment_detail_id,0,$result);
            return ['status' => 0, 'data' => $result, 'messages' => [(isset($result["message"]) ? $result['message'] : "2فشلت العملية")]];
    }



    public function otp($payment_detail_id,$ref_id, $otp)
    {
        if (empty($otp))
            return ['status' => 0, 'data' => array(), 'messages' => ["يرجى ادخال رمز التاكيد"]];
        if (empty($payment_detail_id))
            return ['status' => 0, 'data' => array(), 'messages' => ["مرجع العملية ضروري"]];
        if ( empty($ref_id))
            return ['status' => 0, 'data' => array(), 'messages' => ["رقم المرجع ضروري"]];

        $url = self::url . '/api/v1/merchant/p2mcl/confirm';
        $header = array(
            "Authorization:Bearer " . $this->data[KeysData::Key],
            "Content-Type: application/json",
            "x-channel: merchant",
            "Accept: application/json"
        );
        $params = [];
        foreach ($this->data as $key => $value) {
                $params[$key] = $value['value'];
        }

        $params["otp"] = $otp;
        $params["purchase_id"] = $ref_id;
        $result = Http::withHeaders($header)
            ->withoutVerifying()
            ->post($url,$params);
        if (isset($result["is_success"]) && $result["is_success"]) {
            $this->updateDetailOtp($payment_detail_id, 2, $result);
            $validate_result = $this->validate($result, $payment_detail_id);
            if ($validate_result['status']) {
                return ['status' => 1, 'data' => array(
                    "payment_detail_id" => $payment_detail_id,
                    "ref_id" => $ref_id,
                ), 'messages' => ["نجحت عملية الدفع"]];
            }else
                return $validate_result;
        } else
            $this->updateDetailOtp($payment_detail_id, 0, $result);
            return ['status' => 0, 'data' => $result, 'messages' => [(isset($result["message"]) ? $result['message'] : "فشلت العملية")]];
    }

    public function validate($data,$payment_detail_id)
    {

        $params = [];
        if (empty($data['transaction_id']))
            return ['status' => 0, 'data' => array(), 'messages' => ["رقم العملية ضروري"]];

        if (empty($data['ref_id']))
            return ['status' => 0, 'data' => array(), 'messages' => ["رقم مرجع العملية ضروري"]];

        $params["transaction_id"] = $data['transaction_id'];
        $params["ref_id"] = $data['ref_id'];
        foreach ($this->data as $key => $value) {
                $params[$key] = $value['value'];
        }
        $header = array(
            "Content-Type: application/json"
        );
        $url = self::url . '/api/merchant/v1/check_status';
        $result = Http::withHeaders($header)
            ->withoutVerifying()
            ->post($url,$params);
        if (isset($result["status"]) && $result["status"] == "Completed") {
            $this->updateDetailValidate($payment_detail_id, 1, $result);
            return ['status' => 1, 'data' => array(
                "purchase_id" => $result["purchase_id"],
                "amount" => $result["amount"],
                "status" => $result["status"]
            ), 'messages' => ["تمت عملية الدفع بنجاح"]];
        }else
            $this->updateDetailValidate($payment_detail_id, 0, $result);
            return ['status' => 0, 'data' => $result, 'messages' => [(isset($result["message"]) ? $result['message'] : "4فشلت العملية")]];

    }


}
