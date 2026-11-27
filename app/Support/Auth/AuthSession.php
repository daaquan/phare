<?php

namespace App\Support\Auth;

use App\Models\User;
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
        self::VERSION_KEY,
        'auth.password_confirmed_at',
        'login.id',
        'login.at',
        'url.intended',
        'passkey.creation',
        'passkey.request',
    ];

    /** Session key holding the User::session_version this session was issued for. */
    public const VERSION_KEY = 'auth.session_version';

    /**
     * Sign the user in and stamp the session with their current session_version.
     * Use this instead of Auth::login() so a later password change can end
     * every other session of the account.
     */
    public static function login(User $user): void
    {
        Auth::login($user);
        self::stamp($user);
    }

    /**
     * Hold a password-verified login at the two-factor challenge, with a start time.
     */
    public static function beginTwoFactor(User $user): void
    {
        $session = app('session');
        $session->set('login.id', $user->id);
        $session->set('login.at', time());
    }

    /**
     * The user id waiting at the two-factor challenge, or null when there is none
     * or it is older than auth.two_factor_timeout (a missing start time counts as
     * expired). An expired entry is dropped so the password must be entered again.
     */
    public static function pendingTwoFactorId(): ?int
    {
        $session = app('session');
        $id = $session->get('login.id');
        if ($id === null) {
            return null;
        }

        $age = time() - (int)$session->get('login.at', 0);
        if ($age < 0 || $age > (int)config('auth.two_factor_timeout', 300)) {
            $session->remove('login.id');
            $session->remove('login.at');

            return null;
        }

        return (int)$id;
    }

    public static function stamp(User $user): void
    {
        app('session')->set(self::VERSION_KEY, $user->sessionVersion());
    }

    /**
     * Sign out a session issued before the account's last password change
     * (or without a stamp at all). Returns false when it did so.
     */
    public static function enforceVersion(): bool
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return true;
        }

        $stamp = app('session')->get(self::VERSION_KEY);
        if ($stamp !== null && (int)$stamp === $user->sessionVersion()) {
            return true;
        }

        self::logout();

        return false;
    }

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
