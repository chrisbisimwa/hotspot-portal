<?php

return [
    'default_provider' => env('PAYMENT_PROVIDER', 'serdipay'),
    
    'serdipay' => [
        'base_url' => env('SERDIPAY_BASE_URL', 'https://api.serdipay.com'),
        'public_key' => env('SERDIPAY_PUBLIC_KEY'),
        'secret_key' => env('SERDIPAY_SECRET_KEY'),
        'webhook_secret' => env('SERDIPAY_WEBHOOK_SECRET'),
        'fake' => env('SERDIPAY_FAKE', true),
    ],
];