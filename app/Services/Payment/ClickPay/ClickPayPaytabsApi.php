<?php

namespace App\Services\Payment\ClickPay;

use Paytabscom\Laravel_paytabs\PaytabsApi;
use Paytabscom\Laravel_paytabs\PaytabsHelper;

class ClickPayPaytabsApi extends  PaytabsApi
{

    const BASE_URLS = [
        'ARE' => [
            'title' => 'United Arab Emirates',
            'endpoint' => 'https://secure.clickpay.com.sa/'
        ],
        'SAU' => [
            'title' => 'Saudi Arabia',
            'endpoint' => 'https://secure.clickpay.com.sa/'
        ],
        'OMN' => [
            'title' => 'Oman',
            'endpoint' => 'https://secure.clickpay.com.sa/'
        ],
        'JOR' => [
            'title' => 'Jordan',
            'endpoint' => 'https://secure.clickpay.com.sa/'
        ],
        'EGY' => [
            'title' => 'Egypt',
            'endpoint' => 'https://secure.clickpay.com.sa/'
        ],
        'IRQ' => [
            'title' => 'Iraq',
            'endpoint' => 'https://secure.clickpay.com.sa/'
        ],
        'PSE' => [
            'title' => 'Palestine',
            'endpoint' => 'https://secure.clickpay.com.sa/'
        ],
        'GLOBAL' => [
            'title' => 'Global',
            'endpoint' => 'https://secure.clickpay.com.sa/'
        ],
        // 'DEMO' => [
        //     'title' => 'Demo',
        //     'endpoint' => 'https://paypage.paytabs.com/'
        // ],
    ];



}
