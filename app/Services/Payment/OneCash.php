<?php

namespace App\Services\Payment;

use App\Services\Payment\Concerns\KeysData;
use App\Services\Payment\Concerns\Payment;
use DateTime;
use DateTimeZone;

class OneCash extends Payment
{

    const url = 'https://portal.onecash.com.ye:49049/';

    public static function getKeysData(): array
    {
        return [
            KeysData::UserName => '',
            KeysData::Password => '',
            KeysData::Key => '',
            KeysData::PublicKey => '',
            KeysData::Hmac => '',
            KeysData::RequestId => '',
        ];
    }

    public function createPayment($payment_purchase_id, $userPhone, $amount)
    {

        $header = array(
            "APIKey:" . $this->data[KeysData::Key],
            "Content-Type: application/json"
        );
        $url = self::url . 'INTEG/rest/CheckPay';
        $params = [];
        $params["amount"] = number_format((float)$amount, 1, '.', '');
        $params["oneCutomerWallet"] = str_replace("+", "", $userPhone);
        $params['endUserId'] = str_replace("+", "", $userPhone);
        $params["id"] = $this->data[KeysData::RequestId];
        $params["pass"] = $this->OneCashPasswordEncrption($this->data[KeysData::Password], $this->data[KeysData::PublicKey]);
        $params["invoiceNumber"] = $payment_purchase_id;
        $params["timeStamp"] = (new DateTime('now', new DateTimeZone('UTC')))->format("d-m-Y H:i:s.B");
        $params["hash"] = $this->encode(hash_hmac('sha512', $params["id"] . "#" . $params["oneCutomerWallet"] . "#" . $params["amount"] . "#" . $params['endUserId'] . "#" . $params["timeStamp"], utf8_encode($this->data[KeysData::Hmac]), true));

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
        if (isset($result["exTranRef"]) && isset($result["error"]) && $result["error"] == 0)
            return ['status' => 2, 'data' => array(
                "purchase_id" => $result["exTranRef"],
                "ref_id" => $params["invoiceNumber"],
            ), 'messages' => ["تم ارسال كود الدفع الى رقم تلفونك يرجى ادخال الكود"]];
        else
            return ['status' => 0, 'data' => $result, 'messages' => [(isset($result["message"]) ? $result['message'] : "فشلت العملية")]];

    }

    public function otp($data)
    {

        if (empty($data['otp']))
            return ['status' => 0, 'data' => array(), 'messages' => ["يرجى ادخال رمز التاكيد"]];
        if (empty($data['purchase_id']))
            return ['status' => 0, 'data' => array(), 'messages' => ["رقم الطلب ضروري"]];
        if (empty($data['ref_id']))
            return ['status' => 0, 'data' => array(), 'messages' => ["مرجع العملية ضروري"]];


        $header = array(
            "APIKey:" . $this->data[KeysData::Key],
            "Content-Type: application/json"
        );
        $url = self::url . 'INTEG/rest/ExePay';
        $params = [];
        $params["otp"] = $data['otp'];
        $params["exTranRef"] = $data['purchase_id'];
        $params["oneCutomerWallet"] = str_replace("+", "", $data["phone"]);
        $params['endUserId'] = str_replace("+", "", $data["phone"]);

        $params["id"] = $this->data[KeysData::RequestId];
        $params["pass"] = $this->OneCashPasswordEncrption($this->data[KeysData::Password], $this->data[KeysData::PublicKey]);
        $params["amount"] = number_format((float)$data["amount"], 1, '.', '');
        $params["timeStamp"] = (new DateTime('now', new DateTimeZone('UTC')))->format("d-m-Y H:i:s.B");
        $params["hash"] = $this->encode(hash_hmac('sha512', $params["id"] . "#" . $params["oneCutomerWallet"] . "#" . $params["amount"] . "#" . $params['endUserId'] . "#" . $data["purchase_id"] . "#" . $params["timeStamp"], utf8_encode($this->data[KeysData::Hmac]), true));

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

        if (isset($result["exTranRef"]) && isset($result["error"]) && $result["error"] == 0) {

            $validate_result = $this->validate([
                "exTranRef" => $result["exTranRef"]
            ]);

            if ($validate_result['status']) {
                return ['status' => 1, 'data' => array(
                    "payment_purchase_id" => $data['ref_id'],
                    "details" => $result,
                ), 'messages' => ["نجحت عملية الدفع"]];
            }
        } else
            return ['status' => 0, 'data' => $result, 'messages' => [(isset($result["message"]) ? $result['message'] : "فشلت العملية")]];
    }

    public function validate($data)
    {
        $header = array(
            "APIKey:" . $this->data[KeysData::Key],
            "Content-Type: application/json"
        );
        $url = self::url . 'INTEG/rest/GetPayStatus';
        $params["id"] = $this->data[KeysData::RequestId];
        $params["pass"] = $this->OneCashPasswordEncrption($this->data[KeysData::Password], $this->data[KeysData::PublicKey]);

        if (empty($data['exTranRef']))
            return ['status' => 0, 'data' => array(), 'messages' => ["رقم العملية ضروري"]];

        $params["exTranRef"] = $data['exTranRef'];
        $params["timeStamp"] = (new DateTime('now', new DateTimeZone('UTC')))->format("d-m-Y H:i:s.B");
        $params["hash"] = $this->encode(hash_hmac('sha512', $params["id"] . "#" . $params["exTranRef"] . "#" . $params["timeStamp"], utf8_encode($this->data[KeysData::Hmac]), true));

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

        if (isset($result["paymentStatus"]) && $result["paymentStatus"] == "POSTED")
            return ['status' => 1, 'data' => array(
                "exTranRef" => $result["exTranRef"],
                "amount" => $result["totalAmount"],
                "status" => $result["paymentStatus"]
            ), 'messages' => ["تمت عملية الدفع بنجاح"]];
        else if (isset($result["paymentStatus"]) && $result["paymentStatus"] == "REFUND")
            return ['status' => 1, 'data' => array(
                "exTranRef" => $result["exTranRef"],
                "amount" => $result["totalAmount"],
                "status" => $result["paymentStatus"]
            ), 'messages' => ["مردود"]];
        else
            return ['status' => 0, 'data' => $result, 'messages' => [(isset($result["message"]) ? $result['message'] : "فشلت العملية")]];

    }

    public static function needOtp(): bool
    {
        return true;
    }

    public static function getProviderName(): string
    {
        return 'one_cash';
    }


    public function encode($data)
    {

        return RFC4648::base64Encode($data);
    }

    public function decode($data)
    {
        return RFC4648::base64Decode($data);
    }

    public function OneCashPasswordEncrption($password, $public_key)
    {
        $key = <<<EOF
-----BEGIN PUBLIC KEY-----
{$public_key}
-----END PUBLIC KEY-----
EOF;
        $pubkey = openssl_pkey_get_public($key);
        openssl_public_encrypt($password, $encrypted, $pubkey);
        $data = base64_encode($encrypted);
        return $data;
    }
}
