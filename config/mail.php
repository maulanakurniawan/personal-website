<?php

return [
    'default' => env('MAIL_MAILER', 'log'),

    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'zeptomail' => [
            'transport' => 'zeptomail',
            'api_key' => env('ZEPTOMAIL_API_KEY'),
            'host' => env('ZEPTOMAIL_HOST', 'zoho.com'),
        ],
    ],

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Maulana Kurniawan'),
    ],

    'support' => [
        'address' => env('MAIL_SUPPORT_ADDRESS', env('MAIL_FROM_ADDRESS', 'hello@example.com')),
        'name' => env('MAIL_SUPPORT_NAME', env('MAIL_FROM_NAME', 'Maulana Kurniawan')),
    ],
];
