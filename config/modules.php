<?php

/*
|--------------------------------------------------------------------------
| 起動モジュール
|--------------------------------------------------------------------------
|
| 環境変数でモジュール名を指定します。複数のモジュールを併用し、同じ環境で動作させることができます。
| 例えば、APIとGmTool(Web)の2つのモジュールを同じnginxで動作させる場合、.envは .env.api と
| .env.gmtool の2つを用意する必要があり、configやrouteのキャッシュも別々に生成されます。
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
            \Phox\Providers\DebugWhoopsProvider::class,
            \Phox\Providers\BladeViewProvider::class,
            \Phox\Providers\TranslateProvider::class,
            \App\Providers\AssetsProvider::class,
        ],

        'url' => env('APP_GMTOOL_URL', 'http://localhost'),

        'aliases' => [],
    ],

    'webview' => [
        'prefix' => 'webview',

        'route' => base_path('app/Http/Controllers/Webview'),

        'providers' => [
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
