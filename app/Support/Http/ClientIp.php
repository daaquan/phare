<?php

namespace App\Support\Http;

/**
 * Resolves the real client IP behind trusted reverse proxies.
 *
 * phpfan.net runs cloudflared -> nginx (127.0.0.1) -> php-fpm, so REMOTE_ADDR
 * is 127.0.0.1 for every visitor unless nginx restores it with real_ip. The
 * forwarded headers are only believed when the direct peer is a trusted proxy;
 * any other peer is used as-is, so a client cannot pick its own IP.
 *
 * ponytail: exact-match proxy list, no CIDR ranges; add them if proxies ever
 * sit on a subnet instead of loopback.
 */
final class ClientIp
{
    /**
     * @param array<string, mixed> $server $_SERVER-style array
     * @param array<int, string> $trustedProxies
     */
    public static function resolve(array $server, array $trustedProxies): string
    {
        $remote = (string)($server['REMOTE_ADDR'] ?? '');

        if (!in_array($remote, $trustedProxies, true)) {
            return $remote;
        }

        // Set by Cloudflare at the edge; cloudflared passes it through.
        $cf = trim((string)($server['HTTP_CF_CONNECTING_IP'] ?? ''));
        if (self::isIp($cf)) {
            return $cf;
        }

        // Standard header: walk right-to-left past our own proxies; the first
        // other hop is the address the outermost trusted proxy actually saw.
        $hops = array_reverse(array_map('trim', explode(',', (string)($server['HTTP_X_FORWARDED_FOR'] ?? ''))));
        foreach ($hops as $hop) {
            if (!self::isIp($hop)) {
                break;
            }
            if (!in_array($hop, $trustedProxies, true)) {
                return $hop;
            }
        }

        return $remote;
    }

    /**
     * The client IP of the current request, using `app.trusted_proxies`.
     */
    public static function current(): string
    {
        $proxies = config('app.trusted_proxies');
        $proxies = is_object($proxies) && method_exists($proxies, 'toArray') ? $proxies->toArray() : (array)$proxies;

        return self::resolve($_SERVER, array_values(array_map('strval', $proxies)));
    }

    private static function isIp(string $value): bool
    {
        return $value !== '' && filter_var($value, FILTER_VALIDATE_IP) !== false;
    }
}
