<?php

namespace App\Services\Payment;

use App\Models\PaymentDetail;
use App\Services\Payment\Concerns\KeysData;
use App\Services\Payment\Concerns\Payment;
use Illuminate\Support\Facades\Http;

class Pyes extends Payment
{

    const url = 'http://rem.alamalbank.com:7103/ws/';

    public static function getKeysData(): array
    {
        return [
            KeysData::RequestId => '',
            KeysData::UserName => '',
            KeysData::Password => '',
            KeysData::Category => '',
            KeysData::AccountID => '',
        ];
    }

    public function createPayment($paymentMethodId, $customerPhone, $customerId, $amount)
    {

        $header = array(
            "Authorization:Bearer " . str_replace('"', '', $this->getToken())
        );
        $payment_detail_id = $this->newDetailPayment($paymentMethodId, $customerId);
        $url = static::url . "amb_pos_slt?agentID=" . $this->data[KeysData::RequestId]
            . "&userName=" . $this->data[KeysData::UserName] . "&userPWD=" . $this->data[KeysData::Password]
            . "&agent_CAT=" . $this->data[KeysData::Category] . "&amount=" . $amount . "&computer_serial=123&"
            . "p_FROM_ACCOUNT_ID=" . str_replace("+967", "", $customerPhone) . "&p_conferm_code=0&ref_id=" . $payment_detail_id . "&to_ACCOUNT_ID=" . $this->data[KeysData::AccountID];
        $result = Http::withHeaders($header)
            ->withoutVerifying()
            ->post($url);
        if (isset($result["Code"]) && $result["Code"] == "006") {
            $this->updateDetailPayment($payment_detail_id, 2, $result);
            return ['status' => 2, 'data' => array(
                "payment_detail_id" => $payment_detail_id,
                "ref_id" => $payment_detail_id,
            ), 'messages' => ["تم ارسال كود الدفع الى رقم تلفونك يرجى ادخال الكود"]];
        } else
            $this->updateDetailPayment($payment_detail_id, 0, $result);
        return ['status' => 0, 'data' => $result, 'messages' => [(isset($result["Message"]) ? $result['Message'] : "فشلت العملية")]];

    }

    public function otp($payment_detail_id,$ref_id, $otp, $amount, $userPhone)
    {
        if (empty($otp))
            return ['status' => 0, 'data' => array(), 'messages' => ["يرجى ادخال رمز التاكيد"]];
        if (empty($payment_detail_id))
            return ['status' => 0, 'data' => array(), 'messages' => ["مرجع العملية ضروري"]];
        if ( empty($ref_id))
            return ['status' => 0, 'data' => array(), 'messages' => ["رقم المرجع ضروري"]];

        $url = static::url . "amb_pos_slt?agentID=" . $this->data[KeysData::RequestId]
            . "&userName=" . $this->data[KeysData::UserName] . "&userPWD=" . $this->data[KeysData::Password]
            . "&agent_CAT=" . $this->data[KeysData::Category] . "&amount=" . $amount . "&computer_serial=1234&"
            . "p_FROM_ACCOUNT_ID=" . str_replace("+967", "", $userPhone)
            . "&p_conferm_code=" . $otp . "&ref_id=" . $payment_detail_id
            . "&to_ACCOUNT_ID=" . $this->data[KeysData::AccountID];
        $token = $this->getToken();
        $header = array(
            "Authorization:Bearer " . str_replace('"', '', $this->getToken())
        );
        $result = Http::withHeaders($header)
            ->withoutVerifying()
            ->post($url);
        if (isset($result["Code"]) && $result["Code"] == "004") {
            $this->updateDetailOtp($payment_detail_id, 2, $result);
            $validate_result = $this->validate(str_replace('"', '', $token), $payment_detail_id);
            if ($validate_result['status']) {
                return ['status' => 1, 'data' => array(
                    "payment_detail_id" => $payment_detail_id,
                    "ref_id" => $payment_detail_id,
                ), 'messages' => ["نجحت عملية الدفع"]];
            }else
                return $validate_result;
        } else
            $this->updateDetailOtp($payment_detail_id, 0, $result);
        return ['status' => 0, 'data' => $result, 'messages' => [(isset($result["message"]) ? $result['message'] : "فشلت العملية")]];

    }

    public function validate($token, $payment_detail_id)
    {
        $header = array(
            "Authorization:Bearer " . $token
        );
        if (empty($payment_detail_id))
            return ['status' => 0, 'data' => array(), 'messages' => ["رقم مرجع العملية ضروري"]];
        $payment_detail = PaymentDetail::find($payment_detail_id);
        if (empty($payment_detail))
            return ['status' => 0, 'data' => array(), 'messages' => ["رقم مرجع غير صحيح"]];
        $url = static::url . "opration_status?agentID=" . $this->data[KeysData::RequestId]
            . "&userName=" . $this->data[KeysData::UserName] . "&userPWD=" . $this->data[KeysData::Password]
            . "&agent_CAT=" . $this->data[KeysData::Category] . "&computer_serial=123&"
            . "p_conferm_code=0&ref_id=" . $payment_detail_id
            . "&pos_id=" . $this->data[KeysData::PosID];

        $result = Http::withHeaders($header)
            ->withoutVerifying()
            ->post($url);

        if (isset($result["Code"]) && $result["Code"] == "004") {
            $this->updateDetailValidate($payment_detail_id, 1, $result);
            return ['status' => 1, 'data' => array(
                "Code" => $result["Code"],
                "Message" => $result["Message"]
            ), 'messages' => ["تمت عملية الدفع بنجاح"]];
        } else
            $this->updateDetailValidate($payment_detail_id, 0, $result);
        return ['status' => 0, 'data' => $result, 'messages' => [(isset($result["Message"]) ? $result['Message'] : "فشلت العملية")]];

    }

    public static function needOtp(): bool
    {
        return true;
    }


    public static function getProviderName(): string
    {
        return 'pyes';
    }

    private function getToken()
    {
        return Http::withHeaders(array(
            "Content-Type: application/json"
        ))
            ->withoutVerifying()
            ->post(static::url . "amp_pos_login?agentID=" . $this->data[KeysData::RequestId] . "&userName=" . $this->data[KeysData::UserName] . "&userPWD=" . $this->data[KeysData::Password]);
    }
}
