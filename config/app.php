<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application name
    |--------------------------------------------------------------------------
    |
    | The name identifying this application. Used wherever it needs to be
    | placed, such as notifications.
    |
    */

    'name' => env('APP_NAME', 'App'),

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | Set through an environment variable. It may decide how the various
    | services are configured.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Startup Module Name
    |--------------------------------------------------------------------------
    |
    | Specify the module name through an environment variable. Multiple modules can run in the same environment.
    | For example, to run API and GmTool (Web) modules on the same nginx instance, prepare .env.api and
    | .env.gmtool files. Configuration and route caches are generated separately for each module.
    |
    */

    'module' => env('APP_MODULE', 'api'),

    'coexistence' => env('APP_COEXISTENCE', false),

    /*
    |--------------------------------------------------------------------------
    | Debug mode
    |--------------------------------------------------------------------------
    |
    | When enabled, error details are displayed.
    | Keeping this off in production is recommended.
    |
    */

    'debug' => (bool)env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | Used to generate URLs when running Artisan commands from the console.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Timezone
    |--------------------------------------------------------------------------
    |
    | Used by the PHP date and time functions.
    |
    */

    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Locale
    |--------------------------------------------------------------------------
    |
    | The default locale used by the translation service provider.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Fallback locale
    |--------------------------------------------------------------------------
    |
    | The locale used when the current one is unavailable.
    |
    */

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Faker locale
    |--------------------------------------------------------------------------
    |
    | The locale the Faker PHP library uses when generating database seeds.
    | It produces localised data such as phone numbers and addresses.
    |
    */

    'faker_locale' => 'ja_JP',

    /*
    |--------------------------------------------------------------------------
    | Encryption key
    |--------------------------------------------------------------------------
    |
    | Used by the encryption service; set it to a random binary string.
    | Without it the encrypted strings are not secure.
    | Always set this before deploying the application.
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Phalcon settings
    |--------------------------------------------------------------------------
    |
    | Overrides for the Phalcon framework defaults.
    |
    */

    'phalcon' => [
        // https://docs.phalcon.io/5.0/ja-jp/db-models
        'orm.enable_implicit_joins' => false, // Enable implicit joins using model relationships
        'orm.exception_on_failed_save' => true, // Throw an exception when saving a model fails
        'orm.force_casting' => false, // Cast values retrieved from the database
        'orm.ignore_unknown_columns' => true, // Ignore columns not defined in the model
        'orm.not_null_validations' => true, // Validate NOT NULL model properties
        'orm.resultset_prefetch_records' => '0', // Number of records to prefetch
        'orm.update_snapshot_on_save' => true, // Update the model snapshot on save
        'orm.virtual_foreign_keys' => false, // Enable virtual foreign keys
        'warning.enable' => false, // Enable warnings
    ],

    /*
    |--------------------------------------------------------------------------
    | Service providers
    |--------------------------------------------------------------------------
    |
    | Service providers loaded automatically when the application boots.
    | Add your own services to this array to extend the application.
    |
    */

    'providers' => [
        \Phox\Providers\ConfigProvider::class,
        \Phox\Providers\LogServiceProvider::class,
        \Phox\Providers\EventsManagerProvider::class,
        \Phox\Providers\ErrorHandlerProvider::class,
        \Phox\Providers\DebugLoggerProvider::class,
        \Phox\Providers\UrlProvider::class,
        \Phox\Providers\DispatcherProvider::class,
        \Phox\Providers\ViewProvider::class,
        \Phox\Providers\EncrypterProvider::class,
        \Phox\Providers\SecurityProvider::class,
        \Phox\Providers\RandomProvider::class,
        \Phox\Providers\SqidsProvider::class,
        \Phox\Providers\RouteServiceProvider::class,
        \Phox\Providers\RequestProvider::class,
        \Phox\Providers\ResponseProvider::class,
        \Phox\Providers\SessionProvider::class,
        \Phox\Providers\AuthProvider::class,
        \Phox\Providers\ModelProvider::class,
        \Phox\Providers\DatabaseProvider::class,

        \App\Providers\AppServiceProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Aliases
    |--------------------------------------------------------------------------
    |
    | Facade aliases loaded automatically when the application boots.
    | A facade is a shorthand for calling a container-registered service statically:
    | \Auth::check() instead of $app['auth']->check().
    |
    */

    'aliases' => [
        'App' => \Phox\Support\Facades\Application::class,
        'DB' => \Phox\Support\Facades\DB::class,
        'Log' => \Phox\Support\Facades\Log::class,
        'Auth' => \Phox\Support\Facades\Auth::class,
        'Cache' => \Phox\Support\Facades\Cache::class,
        'Security' => \Phox\Support\Facades\Security::class,
        'Request' => \Phox\Support\Facades\Request::class,
        'Response' => \Phox\Support\Facades\Response::class,
        'Session' => \Phox\Support\Facades\Session::class,
        'DebugLogger' => \Phox\Support\Facades\DebugLogger::class,
        'Artisan' => \Phox\Support\Facades\Artisan::class,
        'ID' => \Phox\Support\Facades\Sqids::class,
        //'Route' => \Phox\Support\Facades\Route::class,
    ],
];
