<?php

return [
    'host' => env('MIKROTIK_HOST', '127.0.0.1'),
    'port' => env('MIKROTIK_PORT', 8728),
    'username' => env('MIKROTIK_USERNAME', 'admin'),
    'password' => env('MIKROTIK_PASSWORD', ''),
    'use_ssl' => env('MIKROTIK_USE_SSL', false),
    'timeout' => 10,
    'fake' => env('MIKROTIK_FAKE', false),
];