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
        'orm' => [
            'enable_implicit_joins' => false, // Whether relations between models enable implicit joins
            'exception_on_failed_save' => true, // Whether a failed model save throws
            'force_casting' => false, // Whether values read from the database are cast
            'ignore_unknown_columns' => true, // Whether columns not defined on the model are ignored
            'not_null_validations' => true, // Whether NULL is allowed for properties whose column is NOT NULL
            'resultset_prefetch_records' => '0', // Number of records to prefetch
            'update_snapshot_on_save' => true, // Whether the model snapshot is refreshed on save
            'virtual_foreign_keys' => false, // Whether virtual foreign keys are enabled
            // optional
            'cache_level' => 3, // 0: no cache, 1: metadata only, 2: metadata + resultsets, 3: metadata + resultsets, and queries built from the cache
            'case_insensitive_column_map' => false, // Whether keys are lowercased when building column-name-keyed arrays
            'cast_last_insert_id_to_int' => false, // Whether the last insert id is cast to int
            'cast_on_hydrate' => false, // Whether values are cast during hydration
            'column_renaming' => true, // Whether column renaming is enabled
            'disable_assign_setters' => false, // Whether setters are used when assigning to properties
            'enable_literals' => true, // Whether literal objects are enabled
            'events' => true, // Whether events are enabled
            'exception_on_failed_metadata_save' => true, // Whether a failed metadata save throws
            'late_state_binding' => false, // Late state binding of the Phalcon\Mvc\Model::cloneResultMap() method
            'unique_cache_id' => 3, // Value guaranteeing cache id uniqueness
        ],
        'db' => [
            'escape_identifiers' => 'On', // Escape identifiers in queries
            'force_casting' => 'Off', // Cast values read from the database
        ],
        'warning.enable' => true, // Enable warnings
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
        \Phare\Providers\LogServiceProvider::class,
        \Phare\Providers\EventsManagerProvider::class,
        \Phare\Providers\ErrorHandlerProvider::class,
        \Phare\Providers\DispatcherProvider::class,
        \Phare\Providers\DebugLoggerProvider::class,
        \Phare\Providers\EncrypterProvider::class,
        //\Phare\Providers\ChronosProvider::class,
        //\Phare\Providers\SqidsProvider::class,
        \Phare\Providers\RouteServiceProvider::class,
        \Phare\Providers\RequestProvider::class,
        \Phare\Providers\ResponseProvider::class,
        \Phare\Providers\SessionProvider::class,
        \Phare\Providers\AuthServiceProvider::class,
        \Phare\Providers\ModelProvider::class,
        \Phare\Providers\DatabaseProvider::class,
        \Phare\Providers\QueueServiceProvider::class,

        \Phare\Providers\DebugWhoopsProvider::class,
        \Phare\View\ViewServiceProvider::class,
        \Phare\Providers\TranslateProvider::class,

        \App\Providers\AppServiceProvider::class,
        \App\Providers\SqidsServiceProvider::class,
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
        'App' => \Phare\Support\Facades\Application::class,
        'DB' => \Phare\Support\Facades\DB::class,
        'Log' => \Phare\Support\Facades\Log::class,
        'Auth' => \Phare\Support\Facades\Auth::class,
        'Cache' => \Phare\Support\Facades\Cache::class,
        'Security' => \Phare\Support\Facades\Security::class,
        'Request' => \Phare\Support\Facades\Request::class,
        'Response' => \Phare\Support\Facades\Response::class,
        'Session' => \Phare\Support\Facades\Session::class,
        'DebugLogger' => \Phare\Support\Facades\DebugLogger::class,
        'Artisan' => \Phare\Support\Facades\Artisan::class,
        'ID' => \Phare\Support\Facades\Sqids::class,
        //'Route' => \Phare\Support\Facades\Route::class,
    ],
];
