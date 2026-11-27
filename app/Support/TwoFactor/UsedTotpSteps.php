<?php

namespace App\Support\TwoFactor;

use App\Support\Concurrency\FileLock;

/**
 * Remembers the last TOTP time step each user signed in with, so a code
 * cannot be replayed inside its +/-1 step (90 s) acceptance window.
 *
 * ponytail: kept in the cache, not a users column; a cache flush re-opens the
 * replay window for at most 90 s. Move to a DB column if that matters.
 */
final class UsedTotpSteps
{
    private const TTL_SECONDS = 300;

    /**
     * Accept the step if it is newer than the last one used, and record it.
     * The check-and-record is one critical section, so two concurrent logins
     * with the same code cannot both succeed. Fails closed: when the cache or
     * lock is unavailable the code is refused (and logged), never replayable.
     */
    public static function claim(int $userId, int $step): bool
    {
        $key = 'totp_last_step_' . $userId; // no ':' -- Phalcon cache keys reject it

        try {
            return FileLock::run($key, function () use ($key, $step): bool {
                $cache = app()->make('cache');
                if ($step <= (int)$cache->get($key, 0)) {
                    return false;
                }
                if (!$cache->put($key, $step, self::TTL_SECONDS)) {
                    throw new \RuntimeException('TOTP step could not be stored.');
                }

                return true;
            });
        } catch (\Throwable $e) {
            error_log('UsedTotpSteps: cache unavailable, refusing code: ' . $e->getMessage());

            return false;
        }
    }
}
