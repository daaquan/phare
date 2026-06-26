<?php

return [

    // 既定のブロードキャスター。Soketi を使うため pusher を既定にする。
    // ローカルや CI で配信を黙らせたい場合は BROADCAST_DRIVER=null。
    'default' => env('BROADCAST_DRIVER', 'null'),

    'connections' => [

        // Soketi (Pusher プロトコル互換) への接続。host/port/scheme を env で
        // 差し替えることで SaaS Pusher にも切り替えられる。
        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
                // Soketi をローカル http で動かす場合は false。
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
