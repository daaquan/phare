<?php

namespace App\Support\Auth;

use Phare\Support\Facades\Auth;

/**
 * Session hygiene around authentication changes. The framework's
 * Auth::logout() only removes the auth id ("so CSRF tokens, flash data and
 * other session state survive"), which left password confirmations, pending
 * two-factor logins and WebAuthn challenges for the next person on the browser.
 */
final class AuthSession
{
    /** Session keys that grant or stage privileges. */
    public const PRIVILEGED_KEYS = [
        'auth.password_confirmed_at',
        'login.id',
        'url.intended',
        'passkey.creation',
        'passkey.request',
    ];

    /**
     * Log out, drop privileged state, and move to a new session id (the old id
     * is deleted server-side). CSRF token and flash data survive.
     */
    public static function logout(): void
    {
        Auth::logout();
        self::forgetPrivilegedState();
        app('session')->regenerateId(true);
    }

    public static function forgetPrivilegedState(): void
    {
        $session = app('session');
        foreach (self::PRIVILEGED_KEYS as $key) {
            $session->remove($key);
        }
    }
}
