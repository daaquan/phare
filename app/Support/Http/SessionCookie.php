<?php

namespace App\Support\Http;

/**
 * Hardens PHP's native session cookie (Phalcon's session manager uses it as-is).
 * Must run before the session starts: once a session is active PHP ignores
 * cookie parameter changes.
 */
final class SessionCookie
{
    private const SAME_SITE = ['lax' => 'Lax', 'strict' => 'Strict', 'none' => 'None'];

    /**
     * Translate the `session.cookie` config into php.ini session settings.
     *
     * @param array<string, mixed> $cookie
     * @return array<string, string>
     */
    public static function iniSettings(array $cookie): array
    {
        $sameSite = self::SAME_SITE[strtolower((string)($cookie['same_site'] ?? 'lax'))] ?? 'Lax';
        $secure = (bool)($cookie['secure'] ?? true);

        // Browsers drop SameSite=None cookies that are not Secure; fall back to Lax.
        if ($sameSite === 'None' && !$secure) {
            $sameSite = 'Lax';
        }

        return [
            'session.name' => (string)($cookie['name'] ?? 'PHPSESSID'),
            'session.cookie_path' => (string)($cookie['path'] ?? '/'),
            'session.cookie_domain' => (string)($cookie['domain'] ?? ''),
            'session.cookie_secure' => $secure ? '1' : '0',
            'session.cookie_httponly' => ($cookie['http_only'] ?? true) ? '1' : '0',
            'session.cookie_samesite' => $sameSite,
            // Refuse session ids the server never issued (session fixation).
            'session.use_strict_mode' => '1',
            'session.use_only_cookies' => '1',
            'session.use_trans_sid' => '0',
        ];
    }

    /**
     * Apply the settings. Returns false when a session is already active.
     *
     * @param array<string, mixed> $cookie
     */
    public static function apply(array $cookie): bool
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return false;
        }

        foreach (self::iniSettings($cookie) as $key => $value) {
            ini_set($key, $value);
        }

        return true;
    }
}
