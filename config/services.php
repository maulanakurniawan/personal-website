<?php

return [
    'google_analytics' => [
        'measurement_id' => env('GOOGLE_ANALYTICS_MEASUREMENT_ID', 'G-CD4QLCV9LG'),
    ],
    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
    ],
];
