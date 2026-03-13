<?php

return [
    'environment' => env('PADDLE_ENVIRONMENT', 'sandbox'),
    'api_key' => env('PADDLE_API_KEY'),
    'client_side_token' => env('PADDLE_CLIENT_TOKEN'),
    'webhook_secret' => env('PADDLE_WEBHOOK_SECRET'),
    'product_id' => env('PADDLE_PRODUCT_ID'),
    'prices' => [
        'starter' => env('PADDLE_PRICE_ID_STARTER'),
        'pro' => env('PADDLE_PRICE_ID_PRO'),
    ],
];
