<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'App'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool)env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'ja_JP',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    'phalcon' => [
        // https://docs.phalcon.io/5.0/ja-jp/db-models
        'orm.enable_implicit_joins' => false, // モデル間の関連を使用して、暗黙の結合を有効にするかどうか
        'orm.exception_on_failed_save' => true, // モデルの保存に失敗した場合に例外をスローするかどうか
        'orm.force_casting' => false, // データベースから取得した値をキャストするかどうか
        'orm.ignore_unknown_columns' => true, // モデルに定義されていないカラムを無視するかどうか
        'orm.not_null_validations' => true, // モデルのプロパティがNOT NULLである場合に、NULLを許可するかどうか
        'orm.resultset_prefetch_records' => "0", // プリフェッチするレコード数
        'orm.update_snapshot_on_save' => true, // モデルのスナップショットを更新するかどうか
        'orm.virtual_foreign_keys' => false, // 仮想外部キーを有効にするかどうか
        'warning.enable' => false, // ワーニングを有効にする
    ],

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [
        \Framework\Providers\ConfigProvider::class,
        \Framework\Providers\LogServiceProvider::class,
        \Framework\Providers\EventsManagerProvider::class,
        \Framework\Providers\ErrorHandlerProvider::class,
        \Framework\Providers\DebugLoggerProvider::class,
        \Framework\Providers\UrlProvider::class,
        \Framework\Providers\EncrypterProvider::class,
        \Framework\Providers\SecurityProvider::class,
        \Framework\Providers\RandomProvider::class,
        \Framework\Providers\SqidsProvider::class,
        \Framework\Providers\DispatcherProvider::class,
        \Framework\Providers\RouteProvider::class,
        \Framework\Providers\RequestProvider::class,
        \Framework\Providers\ResponseProvider::class,
        \Framework\Providers\SessionProvider::class,
        \Framework\Providers\AuthProvider::class,
        \Framework\Providers\ModelProvider::class,
        \Framework\Providers\DatabaseProvider::class,

        \App\Providers\AppServiceProvider::class,
    ],

    'aliases' => [
        'App' => \Framework\Support\Facades\Application::class,
        'DB' => \Framework\Support\Facades\DB::class,
        'Log' => \Framework\Support\Facades\Log::class,
        'Auth' => \Framework\Support\Facades\Auth::class,
        'Security' => \Framework\Support\Facades\Security::class,
        'Request' => \Framework\Support\Facades\Request::class,
        'Response' => \Framework\Support\Facades\Response::class,
        'DebugLogger' => \Framework\Support\Facades\DebugLogger::class,
        'Artisan' => \Framework\Support\Facades\Artisan::class,
        'ID' => \Framework\Support\Facades\Sqids::class,
    ]
];
