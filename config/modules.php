<?php

/*
|--------------------------------------------------------------------------
| Startup Module
|--------------------------------------------------------------------------
|
| Specify the module name through an environment variable. Multiple modules can run in the same environment.
| For example, to run API and Web modules on the same nginx instance, prepare .env.api and
| .env.web files. Configuration and route caches are generated separately for each module.
|
*/

return [

    'api' => [
        'prefix' => 'api',

        'route' => [
            base_path('routes/api.php'),
            base_path('routes/raid.php'),
        ],

        'url' => env('APP_API_URL', 'http://localhost'),

        'providers' => [],
        'aliases' => [],
    ],

    'web' => [
        'route' => base_path('app/Http/Controllers/Web'),

        'providers' => [
            \Phox\Providers\DebugWhoopsProvider::class,
            \Phox\Providers\BladeViewProvider::class,
            \Phox\Providers\TranslateProvider::class,
            \App\Providers\AssetsProvider::class,
        ],

        'url' => env('APP_WEB_URL', 'http://localhost'),

        'aliases' => [],
    ],

    'callback' => [
        'prefix' => 'callback',

        'route' => [
            base_path('callbacks'),
        ],

        'providers' => [],

        'url' => env('APP_CALLBACK_URL', 'http://localhost'),

        'aliases' => [],
    ],
];
