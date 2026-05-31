<?php

return [

    /*
    |--------------------------------------------------------------------------
    | TriPay Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for TriPay closed payment integration.
    | Set TRIPAY_IS_ENABLED=true in .env to activate.
    |
    */

    'is_enabled' => env('TRIPAY_IS_ENABLED', false),

    'api_key'       => env('TRIPAY_API_KEY', ''),
    'private_key'   => env('TRIPAY_PRIVATE_KEY', ''),
    'merchant_code' => env('TRIPAY_MERCHANT_CODE', ''),

    // 'sandbox' or 'production'
    'mode' => env('TRIPAY_MODE', 'sandbox'),

    'base_url' => [
        'sandbox'    => 'https://tripay.co.id/api-sandbox',
        'production' => 'https://tripay.co.id/api',
    ],

    /*
    |--------------------------------------------------------------------------
    | Anti-Bot Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Format: "max_attempts,decay_minutes"
    | e.g. "5,10" = 5 requests per 10 minutes per IP
    |
    */

    'rate_limit' => [
        'checkout' => env('RATE_LIMIT_CHECKOUT', '5,10'),
        'download' => env('RATE_LIMIT_DOWNLOAD', '10,10'),
        'contact'  => env('RATE_LIMIT_CONTACT', '3,10'),
        'lookup'   => env('RATE_LIMIT_LOOKUP', '10,10'),
    ],

];
