<?php

namespace App\Notifications\Sms;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class SmsClient
{
    /**
     * @var string Sms's send API endpoint
     */
    protected string $sendEndpoint = 'https://app.mobile.net.sa/api/v1/send';

    /** @var string
     * SMS username
     */
    protected $username;

    /** @var string
     * SMS password
     */
    protected $password;

    /** @var string
     * SMS sender
     */
    protected $sender;


    /** @var string
     * SMS key
     */
    protected $key;

    /**
     * SmsClient constructor.
     *
     */
    public function __construct()
    {
//        $this->username = config('sms.username');
//        $this->password = config('sms.password');
        $this->sender = config('sms.sender');
        $this->key = config('sms.key');

        return $this;
    }

    /**
     *  SmsClient send method.
     *
     * @param $to
     * @param $message
     *
     * @return mixed Sms API result
     * @throws GuzzleException
     * @throws ConnectionException
     *
     */
    public function send($to, $message)
    {
//        $client = new Client();
        $header = [
            'Content-Type' => 'application/json; charset=utf-8',
            'x-channel' => 'merchant',
            'Authorization' => 'Bearer ' . $this->key,
        ];
//        $params = [
//            'user' => $this->username,
//            'pass' => $this->password,
//            'sender' => $this->sender,
//            'to' => $to,
//            'message' => $message,
////            'unicode' => 'u',
////                'flash'     => 0,
//
//        ];

        $body = [
            'senderName' => $this->sender,
            'number' => $to,
            'messageBody' => $message,
            'sendAtOption' => 'Now',
        ];

        $result = Http::withHeaders($header)
            ->withoutVerifying()
            ->post($this->sendEndpoint, $body);


        if (!$result) {
            \Log::warning("SmsClient: Unknown Response or Request Failed");
            return [];
        }

        return $this->parseResponse($result);
    }


//    public function send2($to, $message)
//    {
//        $curl = curl_init();
//        curl_setopt_array($curl, array(
//            CURLOPT_URL => 'https://www.jawalbsms.ws/api.php/sendsms?user=BARAA&pass=Aa@0991614655&sender=BARAA&to=' . $to . '&message=' . $message,
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_ENCODING => '',
//            CURLOPT_MAXREDIRS => 10,
//            CURLOPT_TIMEOUT => 0,
//            CURLOPT_FOLLOWLOCATION => true,
//            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//            CURLOPT_CUSTOMREQUEST => 'GET',
////            CURLOPT_POSTFIELDS => array('user' => 'BARAA-AD','pass' => 'Aa@0991614655','sender' => 'BARAA-AD','to' => '966531580302','message' => 'للاطلاع على مواعيد المقابلات  قم بزيارة الرابط','unicode' => 'u'),
//            CURLOPT_HTTPHEADER => array(
//                'Accept: application/json'
//            ),
//        ));
//
//        $response = curl_exec($curl);
//
//        curl_close($curl);
//        echo $response;

//        $curl = new Curl();
//
//        $username = "BARAA";        // The user name of gateway
//        $password = "Aa@0991614655";            // the password of gateway
//        $destinations = "966533209532"; // 966500000000,966510000000
//        $message = "test message";
//        $sender = "BARAA";
//
//        $url = "http://www.jawalbsms.ws/api.php/sendsms?user=$username&pass=$password&to=$destinations&message=$message&sender=$sender";
//
//
//        $urlDiv = explode("?", $url);
//        $result = $curl->_simple_call("post", $urlDiv[0], $urlDiv[1], array("TIMEOUT" => 3));
//        echo $result;
//    }

    /**
     * @param $res
     *
     * @return mixed|null
     */
    public function parseResponse($res)
    {
        $body = $res->getBody()->getContents();
        if ($this->isJson($body))
            return json_decode($body) ?? [];
        else
            return trim(trim($body), "\xEF\xBB\xBF");
    }

    function isJson($string): bool
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public function errorsCodes(): array
    {
        return $errors = [
            "-100" => "Missing parameters (not exist or empty) Username + password.",
            "-110" => "Account not exist (wrong username or password).",
            "-111" => "The account not activated.",
            "-112" => "Blocked account.",
            "-113" => "Not enough balance.",
            "-114" => "The service not available for now.",
            "-115" => "The sender not available (if user have no opened sender).",
            "-116" => "Invalid sender name",
            "-120" => "No destination addresses, or all destinations are not correct"
        ];

    }
}
