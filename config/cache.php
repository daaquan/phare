<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Cache Store
    |--------------------------------------------------------------------------
    |
    | Backs the rate limiter (App\Http\Middleware\ThrottleRequests). The file
    | store needs storage/framework/cache/data writable by the php-fpm user.
    |
    | Supported: "file", "redis", "array", "null"
    |
    */

    'default' => env('CACHE_DRIVER', 'file'),

    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
        ],

        'array' => [
            'driver' => 'array',
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],
    ],

    'prefix' => env('CACHE_PREFIX', 'phare_cache_'),

];
