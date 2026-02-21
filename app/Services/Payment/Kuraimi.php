<?php

namespace App\Services\Payment;

use App\Services\Payment\Concerns\KeysData;
use App\Services\Payment\Concerns\Payment;

class Kuraimi extends Payment
{

    public static function getKeysData(): array
    {
        return [];
    }

    public static function needUserPassword(): bool
    {
        return true;
    }


    public static function getProviderName(): string
    {
        return 'kuraimi_haseb';
    }


    public function createPayment($payment_purchase_id, $userPhone, $amount)
    {
        $header = array(
            "Authorization:Bearer " . $this->data[KeysData::Key],
            "Content-Type: application/json",
            "x-channel: merchant",
            "Accept: application/json"
        );
        $url = $this->data[KeysData::Url] . '/api/v1/merchant/p2mcl';

        $params['ref_id'] = $payment_purchase_id;
        $params["source_wallet_id"] = $this->data[KeysData::PublicKey];
        $params["request_id"] = $payment_purchase_id;
        $params["purpose"] = "الدفع عبر " . env("APP_NAME", "") . " برقم " . $payment_purchase_id;
        $params["target_phone"] = str_replace("+", "", $userPhone);
        $params["amount"] = $amount;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        $result = json_decode(curl_exec($ch), true);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $result;
    }

    public function otp($data)
    {

        $url = $this->data[KeysData::Url] . '/api/v1/merchant/p2mcl/confirm';
        $header = array(
            "Authorization:Bearer " . $this->data[KeysData::Key],
            "Content-Type: application/json",
            "x-channel: merchant",
            "Accept: application/json"
        );
        $params = [];
        foreach ($this->data as $key => $value) {
            if ($key != 'url')
                $params[$key] = $value['value'];
        }

        if (!isset($data['otp']) || empty($data['otp']))
            return ['status' => 0, 'data' => array(), 'messages' => ["يرجى ادخال رمز التاكيد"]];
        if (!isset($data['purchase_id']) || empty($data['purchase_id']))
            return ['status' => 0, 'data' => array(), 'messages' => ["رقم الطلب ضروري"]];
        if (!isset($data['ref_id']) || empty($data['ref_id']))
            return ['status' => 0, 'data' => array(), 'messages' => ["مرجع العملية ضروري"]];

        $params["otp"] = $data['otp'];
        $params["purchase_id"] = $data['purchase_id'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        Log::info("$url");
        Log::info(json_encode($params));
        $result = curl_exec($ch);
        Log::info("$result");
        $result = json_decode($result, true);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $result;
    }

    public function validate($data)
    {
        $params = [];
        foreach ($this->data as $key => $value) {
            if ($key != 'url')
                $params[$key] = $value['value'];
        }
        $header = array(
            "Content-Type: application/json"
        );
        $url = $this->data[KeysData::Url] . '/api/merchant/v1/check_status';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        $result = json_decode(curl_exec($ch), true);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $result;
    }

    public function refund($data)
    {
        $params = [];
        foreach ($this->data as $key => $value) {
            if ($key != 'url')
                $params[$key] = $value['value'];
        }
        $url = $this->data[KeysData::Url] . '/api/merchant/v1/refund';

        if (!isset($data['transaction_id']) || empty($data['transaction_id']))
            return ['status' => 0, 'data' => array(), 'messages' => ["رقم العملية ضروري"]];

        if (!isset($data['amount']) || empty($data['amount']))
            return ['status' => 0, 'data' => array(), 'messages' => ["مبلغ العملية ضروري"]];

        $params["transaction_id"] = $data['transaction_id'];
        $params["amount"] = $data['amount'];
        $params['ref_id'] = $data['payment_purchase_id'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        $result = json_decode(curl_exec($ch), true);
        curl_close($ch);
        return $result;
    }
}
