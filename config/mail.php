<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default mailer
    |--------------------------------------------------------------------------
    |
    | The mailer used to send messages. Locally the `log` driver writes them
    | to storage/logs instead of delivering anything.
    |
    */
    // NOTE: avoid passing 'log' as the env() default — Phare defines a log()
    // helper, so Env::get's is_callable() check would invoke it. Coalesce instead.
    'default' => env('MAIL_MAILER') ?: 'log',

    'mailers' => [
        'log' => [
            'driver' => 'log',
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'noreply@phpfan.net'),
                'name' => env('MAIL_FROM_NAME', 'Phare'),
            ],
        ],

        'array' => [
            'driver' => 'array',
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'noreply@phpfan.net'),
                'name' => env('MAIL_FROM_NAME', 'Phare'),
            ],
        ],

        'smtp' => [
            'driver' => 'smtp',
            // `live` gates the real socket; keep false unless explicitly enabled.
            'live' => env('MAIL_LIVE', false),
            'host' => env('MAIL_HOST', 'localhost'),
            'port' => (int)env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME', ''),
            'password' => env('MAIL_PASSWORD', ''),
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'noreply@phpfan.net'),
                'name' => env('MAIL_FROM_NAME', 'Phare'),
            ],
        ],
    ],
];
