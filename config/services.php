<?php

return [
    'google_analytics' => [
        'measurement_id' => env('GOOGLE_ANALYTICS_MEASUREMENT_ID', 'G-CD4QLCV9LG'),
    ],
    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
    ],
    'zeptomail' => [
        'api_key' => env('ZEPTOMAIL_API_KEY'),
        'host' => env('ZEPTOMAIL_HOST', 'zoho.com'),
    ],
];
