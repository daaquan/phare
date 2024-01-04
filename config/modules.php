<?php

/*
|--------------------------------------------------------------------------
| Startup Module
|--------------------------------------------------------------------------
|
| Specify the module name through an environment variable. Multiple modules can run in the same environment.
| For example, to run API and GmTool (Web) modules on the same nginx instance, prepare .env.api and
| .env.gmtool files. Configuration and route caches are generated separately for each module.
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

    'gmtool' => [
        'route' => base_path('app/Http/Controllers/GmTool'),

        'providers' => [
            \Phox\Providers\DispatcherProvider::class,
            \Phox\Providers\DebugWhoopsProvider::class,
            \Phox\Providers\BladeViewProvider::class,
            \Phox\Providers\TranslateProvider::class,
        ],

        'url' => env('APP_GMTOOL_URL', 'http://localhost'),

        'aliases' => [],
    ],

    'webview' => [
        'prefix' => 'webview',

        'route' => base_path('app/Http/Controllers/Webview'),

        'providers' => [
            \Phox\Providers\DispatcherProvider::class,
            \Phox\Providers\DebugWhoopsProvider::class,
            \Phox\Providers\BladeViewProvider::class,
            \Phox\Providers\TranslateProvider::class,
        ],

        'url' => env('APP_WEBVIEW_URL', 'http://localhost'),

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
