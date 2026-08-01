<?php

use App\Models\User;

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication defaults
    |--------------------------------------------------------------------------
    |
    | The default guard for the application and the broker used for password
    | resets. Both may be changed to any other configured value.
    |
    */
    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication guards
    |--------------------------------------------------------------------------
    |
    | Defines the driver and user provider for each guard. Session-based
    | authentication uses the `session` driver.
    |
    */
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User providers
    |--------------------------------------------------------------------------
    |
    | Defines how guards retrieve users. The `eloquent` driver resolves them
    | from the configured model.
    |
    */
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', User::class),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password resets
    |--------------------------------------------------------------------------
    |
    | The table holding reset tokens, their lifetime in minutes, and the
    | throttle in seconds before another link may be requested.
    |
    */
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password confirmation timeout
    |--------------------------------------------------------------------------
    |
    | Seconds before the password confirmation screen asks again.
    |
    */
    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

    /*
    |--------------------------------------------------------------------------
    | Legacy compatibility (single-guard fallback)
    |--------------------------------------------------------------------------
    |
    | Top-level keys AuthManager falls back to when guards/providers cannot be
    | resolved. `session_id` also serves as the web guard session key, so it
    | stays 'auth' to keep existing sessions valid.
    |
    */
    'model' => User::class,
    'session_id' => 'auth',
];
