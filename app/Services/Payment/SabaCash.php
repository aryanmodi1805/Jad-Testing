<?php

namespace App\Services\Payment;

use App\Services\Payment\Concerns\KeysData;
use App\Services\Payment\Concerns\Payment;

class SabaCash extends Payment
{

    const url = 'https://api.sabacash.com:49901/api/';

    public static function getKeysData(): array
    {
        return [
            KeysData::UserName => '',
            KeysData::Password => '',
        ];
    }

    public function createPayment($payment_purchase_id, $userPhone, $amount)
    {

        $header = array(
            "Authorization:Bearer " . str_replace('"', '', $this->getToken())
        );
        $params = [];
        $params['source'] = [];
        $params['source']['currencyId'] = "2";
        $params['beneficiary'] = [];
        $params['beneficiary']['terminal'] = "1";
        $params['beneficiary']['currencyId'] = "2";
        $params["amount"] = $amount;
        $params["amountCurrencyId"] = "2";
        $params["note"] = "الدفع عبر الدفع عبر " . env("APP_NAME", "") . " برقم " . $payment_purchase_id;
        $params['source']['code'] = str_replace("+967", "", $userPhone);

        $header = array(
            "Authorization:Bearer " . str_replace('"', '',$this->getToken()),
            "Content-Type: application/json",
            'Accept-Language: ar'
        );
        $url = static::url.  'accounts/v1/adjustment/onLinePayment';
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
        if (empty($data['otp']))
            return ['status' => 0, 'data' => array(), 'messages' => ["يرجى ادخال رمز التاكيد"]];
        if ( empty($data['purchase_id']))
            return ['status' => 0, 'data' => array(), 'messages' => ["رقم الطلب ضروري"]];
        if (empty($data['ref_id']))
            return ['status' => 0, 'data' => array(), 'messages' => ["مرجع العملية ضروري"]];

        $token = $this->getToken();

        $header = array(
            "Authorization:Bearer " . str_replace('"', '', $token),
            "Content-Type: application/json",
            'Accept-Language: ar'
        );
        $url = static::url.  'accounts/v1/adjustment/onLinePayment';
        $params["id"] = $data['purchase_id'];
        $params["note"] = "تأكيد الدفع عبر " . env("APP_NAME", "") . " برقم " . $data['ref_id'];
        $params["otp"] = $data['otp'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
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

    public static function needOtp(): bool
    {
        return true;
    }

    public static function getProviderName(): string
    {
        return 'saba_cash';
    }

    public function validate($data)
    {
        $header = array(
            "Authorization:Bearer " .  $data["token"],
            "Content-Type: application/json",
            'Accept-Language: ar'
        );
        if ( empty($data['transactionId']))
            return ['status' => 0, 'data' => array(), 'messages' => ["رقم العملية ضروري"]];
        $url = static::url. 'accounts/v1/adjustment/checkAdjustmentByTransactionId';
        $params = [];
        $params["transactionId"] = $data['transactionId'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        $result = json_decode(curl_exec($ch), true);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $result;
    }

    private  function getToken()
    {
        $params = [];
        $params["username"] = $this->data[KeysData::UserName];
        $params["password"] = $this->data[KeysData::Password];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::url."user-ms/v1/login");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,  array(
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        $data = json_decode(curl_exec($ch), true);
        curl_close($ch);
        return $data["token"]??"";
    }
}
