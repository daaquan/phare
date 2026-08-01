<?php

namespace App\Support\TwoFactor;

/**
 * Dependency-free TOTP implementation (RFC 6238, HMAC-SHA1 / 30s / 6 digits).
 * Compatible with standard authenticators such as Google Authenticator and 1Password.
 */
final class Totp
{
    private const BASE32 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    private const PERIOD = 30;

    private const DIGITS = 6;

    /**
     * Generate a random Base32 secret.
     */
    public static function generateSecret(int $length = 16): string
    {
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= self::BASE32[random_int(0, 31)];
        }

        return $secret;
    }

    /**
     * Build the otpauth:// URI an authenticator app registers from.
     */
    public static function provisioningUri(string $secret, string $label, string $issuer): string
    {
        return sprintf(
            'otpauth://totp/%s?secret=%s&issuer=%s&period=%d&digits=%d',
            rawurlencode($issuer . ':' . $label),
            $secret,
            rawurlencode($issuer),
            self::PERIOD,
            self::DIGITS,
        );
    }

    /**
     * Check whether the submitted code is valid for the current time step (including
     * the tolerated window on either side).
     */
    public static function verify(string $secret, string $code, int $window = 1): bool
    {
        $code = trim($code);
        if (!preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $now = time();
        for ($i = -$window; $i <= $window; $i++) {
            $candidate = self::codeAt($secret, $now + ($i * self::PERIOD));
            if (hash_equals($candidate, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compute the 6-digit code for the given UNIX timestamp.
     */
    public static function codeAt(string $secret, int $timestamp): string
    {
        $counter = intdiv($timestamp, self::PERIOD);
        $binCounter = pack('N*', 0) . pack('N*', $counter); // 64bit big-endian
        $key = self::base32Decode($secret);

        $hash = hash_hmac('sha1', $binCounter, $key, true);
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;

        $value = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        $code = $value % (10 ** self::DIGITS);

        return str_pad((string)$code, self::DIGITS, '0', STR_PAD_LEFT);
    }

    private static function base32Decode(string $b32): string
    {
        $b32 = strtoupper(rtrim($b32, '='));
        $buffer = 0;
        $bitsLeft = 0;
        $out = '';

        foreach (str_split($b32) as $char) {
            $val = strpos(self::BASE32, $char);
            if ($val === false) {
                continue;
            }
            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;
            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $out .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $out;
    }
}
