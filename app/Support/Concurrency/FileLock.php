<?php

namespace App\Support\Concurrency;

/**
 * Exclusive, host-wide critical section built on flock(), independent of the
 * cache driver (the file/array stores have no atomic add or increment).
 *
 * It serialises PHP-FPM workers and CLI processes on ONE host. Behind several
 * app hosts sharing a Redis cache, replace callers with Redis INCR / SET NX.
 *
 * Fails closed: if the lock cannot be taken within the timeout the callback
 * does NOT run and a RuntimeException is thrown.
 */
final class FileLock
{
    private const WAIT_MICROSECONDS = 2_000_000;

    private const POLL_MICROSECONDS = 5_000;

    /** Directory for lock files; tests may point it elsewhere. */
    public static ?string $directory = null;

    /**
     * @template T
     *
     * @param callable(): T $callback
     * @return T
     */
    public static function run(string $name, callable $callback): mixed
    {
        $dir = self::$directory ?? storage_path('framework/locks');
        if (!is_dir($dir) && (!is_dir(dirname($dir)) || (!mkdir($dir, 0775) && !is_dir($dir)))) {
            throw new \RuntimeException('Lock directory is not available.');
        }

        $handle = @fopen($dir . '/' . sha1($name) . '.lock', 'c');
        if ($handle === false) {
            throw new \RuntimeException('Lock file cannot be opened.');
        }

        try {
            $deadline = hrtime(true) + self::WAIT_MICROSECONDS * 1000;
            while (!flock($handle, LOCK_EX | LOCK_NB)) {
                if (hrtime(true) >= $deadline) {
                    throw new \RuntimeException('Timed out waiting for lock.');
                }
                usleep(self::POLL_MICROSECONDS);
            }

            return $callback();
        } finally {
            fclose($handle); // releases the lock
        }
    }
}
