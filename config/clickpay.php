<?php


return [

    /*
     |--------------------------------------------------------------------------
     | Merchant profile id
     |--------------------------------------------------------------------------
     |
     | Your merchant profile id , you can find the profile id on your clickPay Merchant’s Dashboard- profile.
     |
    Profile ID 45243
Name Jad services
Merchant ID 4250

    Merchant Name Jad services
    sdk server  key : SGJNMBTMDW-JJR29KMD2N-BG9MMMG69M
    api server key : SRJNMBTMKH-JJR29KMD9D-WLHHLBBJWR
     */

    'profile_id' => env('clickpay_profile_id', 45243),

    /*
   |--------------------------------------------------------------------------
   | Server Key
   |--------------------------------------------------------------------------
   |
   | You can find the Server key on your clickPay Merchant’s Dashboard - Developers - Key management.
   |
   */

    'server_key' => env('clickpay_server_key', 'SRJNMBTMKH-JJR29KMD9D-WLHHLBBJWR'),

    /*
   |--------------------------------------------------------------------------
   | Currency
   |--------------------------------------------------------------------------
   |
   | The currency you registered in with clickPay account
     you must pass value from this array ['SAR','AED','EGP','OMR','JOD','US']
   |
   */

    'currency' => env('clickpay_currency', 'SAR'),
    'region' => env('clickpay_region', 'SAU'),
    'callback_url' => env('clickpay_url_callback', 'https://sa.jad.services/paymentIpnCallback'),

//    'callback' => env('clickpay_ipn_callback', \App\Filament\Seller\Pages\ClickPayPage::class ),

];
