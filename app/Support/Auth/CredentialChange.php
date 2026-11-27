<?php

namespace App\Support\Auth;

use App\Models\User;
use App\Support\Concurrency\FileLock;

/**
 * Serialised credential writes. Password reset tokens are keyed by email, so
 * everything that issues, consumes or revokes one for an address (reset link,
 * reset form, email change, account deletion) runs under the same per-email
 * lock; password writes run under a per-account lock.
 *
 * Both are host-wide flocks that fail closed (RuntimeException when the lock
 * cannot be taken). Behind several app hosts the same checks must move into
 * the database: DELETE ... WHERE email = ? with affected-row count for token
 * consumption, and the conditional UPDATE below already guards the version.
 */
final class CredentialChange
{
    /**
     * @template T
     *
     * @param callable(): T $callback
     * @return T
     */
    public static function withEmailLock(string $email, callable $callback): mixed
    {
        return FileLock::run('reset_email_' . sha1(strtolower(trim($email))), $callback);
    }

    /**
     * Set a new password and bump session_version exactly once for this write.
     * Under the account lock the row is re-read, and the UPDATE only applies
     * while session_version still equals what was read, so two concurrent
     * changes can never end with the same version.
     *
     * @return User|null the reloaded user, or null when nothing was written
     *                   (lock unavailable, account gone, lost the race)
     */
    public static function setPassword(User $user, string $plain): ?User
    {
        try {
            return FileLock::run('password_change_' . (int)$user->id, function () use ($user, $plain): ?User {
                $fresh = User::findFirst((int)$user->id);
                if (!$fresh instanceof User) {
                    return null;
                }

                $version = $fresh->sessionVersion();
                $fresh->password = $plain; // hashed by the model's password attribute handling
                $hash = (string)$fresh->password;

                $db = app()->make($fresh->getWriteConnectionService());
                $db->execute(
                    'UPDATE users SET password = ?, session_version = ?, updated_at = ? WHERE id = ? AND session_version = ?',
                    [$hash, $version + 1, date('Y-m-d H:i:s'), (int)$fresh->id, $version],
                );

                return $db->affectedRows() === 1 ? User::findFirst((int)$fresh->id) : null;
            });
        } catch (\RuntimeException $e) {
            error_log('CredentialChange::setPassword: refused (user=' . (int)$user->id . ')');

            return null;
        }
    }
}
