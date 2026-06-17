<?php

return [
    'enabled' => env('ADMIN_HUB_ENABLED', false),
    'default_product' => env('ADMIN_HUB_DEFAULT_PRODUCT', 'webhookwatch'),
    'products' => [
        'webhookwatch' => [
            'name' => 'WebhookWatch',
            'domain' => 'webhookwatch.com',
            'base_url' => env('ADMIN_HUB_WEBHOOKWATCH_URL'),
            'client_id' => env('ADMIN_HUB_WEBHOOKWATCH_CLIENT_ID'),
            'client_secret' => env('ADMIN_HUB_WEBHOOKWATCH_CLIENT_SECRET'),
        ],
        'solohours' => [
            'name' => 'SoloHours',
            'domain' => 'solohours.com',
            'base_url' => env('ADMIN_HUB_SOLOHOURS_URL'),
            'client_id' => env('ADMIN_HUB_SOLOHOURS_CLIENT_ID'),
            'client_secret' => env('ADMIN_HUB_SOLOHOURS_CLIENT_SECRET'),
        ],
        'maulanakurniawan' => [
            'name' => 'MaulanaKurniawan',
            'domain' => 'maulanakurniawan.com',
            'base_url' => env('ADMIN_HUB_MAULANAKURNIAWAN_URL'),
            'client_id' => env('ADMIN_HUB_MAULANAKURNIAWAN_CLIENT_ID'),
            'client_secret' => env('ADMIN_HUB_MAULANAKURNIAWAN_CLIENT_SECRET'),
        ],
    ],
];
