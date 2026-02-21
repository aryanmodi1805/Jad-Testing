<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme'   => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'apple' => [
        'shared_secret' => env('APPLE_SHARED_SECRET'),
        'bundle_id'     => env('APPLE_BUNDLE_ID'),
        'key_id'        => env('APPLE_KEY_ID'),
        'issuer_id'     => env('APPLE_ISSUER_ID'),
        'private_key'   => env('APPLE_PRIVATE_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Wafeq Accounting
    |--------------------------------------------------------------------------
    */
    'wafeq' => [
        'api_key'          => env('WAFEQ_API_KEY'),
        'enabled'          => env('WAFEQ_ENABLED', false),

        // ✅ REQUIRED for invoice creation
        'revenue_account'  => env('WAFEQ_REVENUE_ACCOUNT'),
        'vat_tax_rate'     => env('WAFEQ_VAT_TAX_RATE'),
    ],

];
