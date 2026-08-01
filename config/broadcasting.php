<?php

return [

    // Default broadcaster. Soketi speaks the Pusher protocol, hence pusher.
    // Set BROADCAST_DRIVER=null to silence broadcasting locally or in CI.
    'default' => env('BROADCAST_DRIVER', 'null'),

    'connections' => [

        // Connection to Soketi (Pusher protocol compatible). Overriding host/port/
        // scheme through env switches this to hosted Pusher.
        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
                // false when Soketi runs over plain http locally.
                'useTLS' => (bool)env('PUSHER_USE_TLS', false),
                'encrypted' => (bool)env('PUSHER_USE_TLS', false),
            ],
            'host' => env('PUSHER_HOST', '127.0.0.1'),
            'port' => (int)env('PUSHER_PORT', 6001),
            'scheme' => env('PUSHER_SCHEME', 'http'),
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

];
