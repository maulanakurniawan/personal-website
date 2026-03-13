<?php

return [
    'default' => env('MAIL_MAILER', 'zeptomail'),
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
        ],
        'zeptomail' => [
            'transport' => 'zeptomail',
            'api_key' => env('ZEPTOMAIL_API_KEY'),
            'host' => env('ZEPTOMAIL_HOST', 'zoho.com'),
        ],
        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],
        'array' => [
            'transport' => 'array',
        ],
    ],
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'alerts@solohours.test'),
        'name' => env('MAIL_FROM_NAME', 'SoloHours'),
    ],

    'alerts' => [
        'from' => [
            'address' => env('MAIL_ALERTS_FROM_ADDRESS', 'alerts@solohours.com'),
            'name' => env('MAIL_ALERTS_FROM_NAME', env('MAIL_FROM_NAME', 'SoloHours')),
        ],
        'reply_to' => [
            'address' => env('MAIL_SUPPORT_ADDRESS', 'support@solohours.com'),
            'name' => env('MAIL_SUPPORT_NAME', 'SoloHours Support'),
        ],
    ],
    'support' => [
        'address' => env('MAIL_SUPPORT_ADDRESS', 'support@solohours.com'),
        'name' => env('MAIL_SUPPORT_NAME', 'SoloHours Support'),
    ],
];
