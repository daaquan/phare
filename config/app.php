<?php

use App\Providers\AppServiceProvider;
use App\Providers\SqidsServiceProvider;
use Phare\Broadcasting\BroadcastServiceProvider;
use Phare\Inertia\InertiaServiceProvider;
use Phare\Mail\MailServiceProvider;
use Phare\Providers\AuthServiceProvider;
use Phare\Providers\DatabaseProvider;
use Phare\Providers\DebugLoggerProvider;
use Phare\Providers\DebugWhoopsProvider;
use Phare\Providers\DispatcherProvider;
use Phare\Providers\EncrypterProvider;
use Phare\Providers\ErrorHandlerProvider;
use Phare\Providers\EventsManagerProvider;
use Phare\Providers\LogServiceProvider;
use Phare\Providers\ModelProvider;
use Phare\Providers\QueueServiceProvider;
use Phare\Providers\RequestProvider;
use Phare\Providers\ResponseProvider;
use Phare\Providers\RouteServiceProvider;
use Phare\Providers\SessionProvider;
use Phare\Providers\TranslateProvider;
use Phare\Support\Facades\Application;
use Phare\Support\Facades\Artisan;
use Phare\Support\Facades\Auth;
use Phare\Support\Facades\Broadcast;
use Phare\Support\Facades\Cache;
use Phare\Support\Facades\DB;
use Phare\Support\Facades\DebugLogger;
use Phare\Support\Facades\Inertia;
use Phare\Support\Facades\Log;
use Phare\Support\Facades\Request;
use Phare\Support\Facades\Response;
use Phare\Support\Facades\Security;
use Phare\Support\Facades\Session;
use Phare\Support\Facades\Sqids;
use Phare\View\ViewServiceProvider;

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
        LogServiceProvider::class,
        EventsManagerProvider::class,
        ErrorHandlerProvider::class,
        DispatcherProvider::class,
        DebugLoggerProvider::class,
        EncrypterProvider::class,
        // \Phare\Providers\ChronosProvider::class,
        // \Phare\Providers\SqidsProvider::class,
        RouteServiceProvider::class,
        RequestProvider::class,
        ResponseProvider::class,
        SessionProvider::class,
        AuthServiceProvider::class,
        ModelProvider::class,
        DatabaseProvider::class,
        QueueServiceProvider::class,
        BroadcastServiceProvider::class,

        DebugWhoopsProvider::class,
        ViewServiceProvider::class,
        InertiaServiceProvider::class,
        MailServiceProvider::class,
        TranslateProvider::class,

        AppServiceProvider::class,
        SqidsServiceProvider::class,
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
        'App' => Application::class,
        'DB' => DB::class,
        'Log' => Log::class,
        'Auth' => Auth::class,
        'Cache' => Cache::class,
        'Security' => Security::class,
        'Request' => Request::class,
        'Response' => Response::class,
        'Session' => Session::class,
        'DebugLogger' => DebugLogger::class,
        'Artisan' => Artisan::class,
        'ID' => Sqids::class,
        'Inertia' => Inertia::class,
        'Broadcast' => Broadcast::class,
        // 'Route' => \Phare\Support\Facades\Route::class,
    ],
];
