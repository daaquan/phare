<?php

namespace App\Providers;

use App\Support\Http\SessionCookie;
use Phare\Support\ServiceProvider;

/**
 * Hardens the session cookie (HttpOnly / Secure / SameSite, config/session.php).
 * Listed before SessionProvider in config/app.php: providers boot as they
 * register, and ViewServiceProvider's boot already starts the session.
 */
class SessionCookieServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $cookie = config('session.cookie');
        $cookie = is_object($cookie) && method_exists($cookie, 'toArray') ? $cookie->toArray() : (array)$cookie;

        // The CLI (tests, artisan) keeps one session alive across app rebuilds.
        if (!SessionCookie::apply($cookie) && PHP_SAPI !== 'cli') {
            error_log('SessionCookieServiceProvider: session already active, cookie flags not applied.');
        }
    }
}
