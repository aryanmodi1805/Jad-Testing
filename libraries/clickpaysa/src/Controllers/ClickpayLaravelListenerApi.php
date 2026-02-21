<?php

namespace Clickpaysa\Laravel_package\Controllers;

use App\Models\PaymentMethod;
use App\Services\Payment\ClickPay;
use App\Services\Payment\ClickPay\PayTabsResponse;
use Clickpaysa\Laravel_package\Services\IpnRequest;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;

class ClickpayLaravelListenerApi extends BaseController
{

    /**
     * RESTful callable action able to receive: callback request\IPN Default Web request from the payment gateway after payment is processed
     */
    public function paymentIpnCallback(Request $request)
    {
        Log::channel('Clickpay')->info("callback request\IPN " . now()->toDateTimeString());
        Log::channel('Clickpay')->info(json_encode($request));
        $content = $request->getContent();
        Log::channel('Clickpay')->info("callback request\IPN  content " . now()->toDateTimeString());
        Log::channel('Clickpay')->info($content);

        $paymentMethod = PaymentMethod::where('type', ClickPay::getProviderName())->firstOrFail();
        $response = (new ClickPay($paymentMethod->details))->successPayment($request->all(),$paymentMethod->id);


        $ipnRequest = new IpnRequest($request);





        DB::table('webhook_calls')->insert([
            'name' => 'clickpay',
            'url' => $request->url(),
            'payload' => json_encode(json_decode($content)),
            'headers' => json_encode($request->headers->all()),
            'exception' => json_encode($ipnRequest->getIpnRequestDetails()),
        ]);


//        $ipnRequest = new IpnRequest($request);


    }


    public function paymentIPN(Request $request)
    {
        try {

            return PayTabsResponse:: updateCartByIPN($request);

            /*   $ipnRequest= new IpnRequest($request);

               $callbackClass = config('clickpay.callback');
               $callback = new $callbackClass();
               if(is_object($callback) && method_exists($callback, 'updateCartByIPN') ){
                   $callback->updateCartByIPN($ipnRequest);
               }
               $response= 'valid IPN request. Cart updated';
               return response($response, 200)
                   ->header('Content-Type', 'text/plain');*/
        } catch (Exception $e) {
            return response($e, 400)
                ->header('Content-Type', 'text/plain');
        }
    }

}
