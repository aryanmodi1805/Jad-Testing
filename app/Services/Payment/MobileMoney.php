<?php

namespace App\Services\Payment;

use App\Services\Payment\Concerns\KeysData;
use App\Services\Payment\Concerns\Payment;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Http;

class MobileMoney  extends Payment
{

    const url = 'https://mobilemoney.com.ye:6446/mmPay/v2/api/pay';

    public static function getKeysData(): array
    {
        return [
            KeysData::Key => '',
            KeysData::UserName => '',
            KeysData::Password => '',
            KeysData::RequestId => '',
            KeysData::BankID => 1,
            KeysData::AppId => 2,
            KeysData::BranchName => "60 street",
        ];
    }

    public function createPayment($payment_purchase_id, $userPhone, $amount)
    {
        $params['ref_id'] = $payment_purchase_id;
        return ['status' => 2, 'data' => array(
            "attach" => [
                "purchase_id" => $params['ref_id'],
                "ref_id" => $params['ref_id'],
            ],
        ), 'messages' => ["يرجى إدخال كود الشراء الذي تم توليده من تطبيق موبايل موني"]];

    }

    public function otp($otp,$purchaseId,$userPhone,$amount,$refId)
    {
        $params = [];
        if ( empty($otp))
            return ['status' => 0, 'data' => array(), 'messages' => ["يرجى ادخال رمز التاكيد"]];
        if ( empty($purchaseId))
            return ['status' => 0, 'data' => array(), 'messages' => ["رقم الطلب ضروري"]];

        $token = $this->getToken();
        $header = array(
            "Authorization:Bearer " . str_replace('"', '', $token),
            "Content-Type: application/json",
            'Connection: keep-alive'
        );
        $params["PurchaseCode"] = $otp;
        $params["BankID"] = $this->data[KeysData::BankID];
        $params["RequestId"] = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 9);
        $params['Merchant'] = [];
        $params['Merchant']['ID'] = $this->data[KeysData::Key];
        $params['Merchant']['AppId'] = $this->data[KeysData::AppId];
        $params['Merchant']['BranchName'] = $this->data[KeysData::BranchName];
        $params['OrderData'] = [];
        $params['OrderData']['Date'] = (new DateTime('now', new DateTimeZone('UTC')))->format("d-m-Y H:i:s.B");
        $params['OrderData']['Title'] = "الدفع عبر " . env("APP_NAME", "") . " برقم " . $purchaseId;
        $params['OrderData']['Ref'] = $purchaseId;
        $params['OrderData']['PeneficialyName'] = $userPhone;
        $params['OrderData']['Amount'] = number_format((float)$amount, 1, '.', '');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        Log::info(self::url);
        Log::info(json_encode($params));
        $result = curl_exec($ch);
        Log::info("$result");
        $result = json_decode($result, true);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (isset($result["ResultCode"]) &&  $result["ResultCode"] == 1) {
            return ['status' => 1, 'data' => array(
                "payment_purchase_id" => $refId,
                "details" => $result,
            ), 'messages' => ["نجحت عملية الدفع"]];
        } else
            return ['status' => 0, 'data' => $result, 'messages' => [(isset($result["ResultMessage"]) ? $result['ResultMessage'] : "تأكد من تفعيل حسابك ووجود رصيد كافي")]];

    }
    public static function needOtp(): bool
    {
        return true;
    }

    public static function getProviderName(): string
    {
        return 'mobile_money';
    }

    private  function getToken()
    {
        $result = Http::withBasicAuth($this->data[KeysData::UserName], $this->data[KeysData::Password])
            ->withHeaders([
                'clientID' => $this->data[KeysData::RequestId],
                'Connection' => 'keep-alive',
                'Content-type' => 'application/json'
            ])
            ->withOptions([
                'verify' => false,
            ])
            ->post("https://mobilemoney.com.ye:6446/mmPay/v2/api/v1/login");
        return $result["access_token"]??"";
    }

}
