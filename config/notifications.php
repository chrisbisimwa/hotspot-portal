<?php

return [
    'default_channel' => env('NOTIFY_DEFAULT_CHANNEL', 'sms'),
    'fallback_channel' => 'email',
    'dispatch_batch' => env('NOTIFY_DISPATCH_BATCH', 50),
];