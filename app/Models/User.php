<?php

namespace App\Models;

use App\Support\Concurrency\FileLock;
use Phare\Auth\Authenticatable;
use Phare\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Phare\Contracts\Auth\CanResetPassword;
use Phare\Eloquent\Concerns\HasTimestamps;
use Phare\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \DateTime|string|null $email_verified_at Reads through the datetime cast; assignment also accepts string|null
 * @property string $password
 * @property \DateTime $birthday
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property \DateTime|string|null $two_factor_confirmed_at
 * @property bool $is_admin
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 */
class User extends Model implements AuthenticatableContract, CanResetPassword
{
    use Authenticatable;
    use HasTimestamps;

    protected ?string $table = 'users';

    protected array $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'birthday',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        // is_admin is deliberately not mass assignable: set it explicitly.
    ];

    protected array $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected array $passwordAttributes = [
        'password',
    ];

    protected array $casts = [
        'email_verified_at' => 'datetime',
        'birthday' => 'date',
        'two_factor_confirmed_at' => 'datetime',
        'is_admin' => 'boolean',
    ];

    /**
     * Whether the user is an admin (drives admin screen access control).
     */
    /** Bumped on password change/reset; see AuthSession::enforceVersion(). */
    public function sessionVersion(): int
    {
        return (int)$this->readAttribute('session_version');
    }

    public function isAdmin(): bool
    {
        // readAttribute returns null instead of throwing for an unset column, which
        // keeps this safe where the is_admin migration has not run (tests included).
        return (bool)$this->readAttribute('is_admin');
    }

    /**
     * A user has many posts.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * A user has many passkeys (WebAuthn credentials).
     */
    public function passkeys()
    {
        return $this->hasMany(Passkey::class);
    }

    public function getEmailForPasswordReset(): string
    {
        return (string)$this->email;
    }

    /**
     * Whether the email address has been verified.
     */
    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null;
    }

    /**
     * Record the email address as verified.
     */
    public function markEmailAsVerified(): void
    {
        $this->email_verified_at = date('Y-m-d H:i:s');
        $this->save();
    }

    /**
     * How long an email verification link stays valid, in seconds.
     */
    public const VERIFICATION_TTL = 3600;

    /**
     * HMAC-SHA256 over id|email|expires keyed by APP_KEY. Unlike the old
     * sha1(email) it cannot be computed from the address alone, it expires, and
     * changing the email invalidates links issued for the previous address.
     */
    public function verificationHash(int $expires): string
    {
        $key = (string)config('app.key');
        if ($key === '') {
            throw new \RuntimeException('APP_KEY must be set to sign email verification links.');
        }

        return hash_hmac(
            'sha256',
            'verify-email|' . (int)$this->id . '|' . strtolower((string)$this->email) . '|' . $expires,
            $key,
        );
    }

    /**
     * The signed path of a fresh verification link.
     */
    public function verificationPath(?int $now = null): string
    {
        $expires = ($now ?? time()) + self::VERIFICATION_TTL;

        return '/user/verify-email/' . (int)$this->id . '/' . $expires . '/' . $this->verificationHash($expires);
    }

    /**
     * Whether a verification link (id / expires / hash) was issued for this
     * user and is still within its lifetime.
     */
    public function hasValidVerificationSignature(string $id, string $expires, string $hash, ?int $now = null): bool
    {
        if (!ctype_digit($id) || !ctype_digit($expires) || (int)$id !== (int)$this->id) {
            return false;
        }

        if ((int)$expires < ($now ?? time())) {
            return false;
        }

        return hash_equals($this->verificationHash((int)$expires), $hash);
    }

    /**
     * Whether two-factor is enabled (i.e. confirmed).
     */
    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_secret !== null
            && $this->two_factor_confirmed_at !== null;
    }

    /** Session key holding freshly generated plaintext codes for a single render. */
    public const NEW_RECOVERY_CODES_KEY = 'two_factor.new_recovery_codes';

    /** Prefix of a hashed recovery-code entry; legacy plaintext entries have none. */
    private const RECOVERY_HASH_PREFIX = 'h1$';

    /**
     * The stored recovery-code entries: keyed hashes, plus any legacy plaintext
     * codes not yet upgraded. Plaintext of a hashed code is not recoverable.
     *
     * @return array<int, string>
     */
    public function recoveryCodes(): array
    {
        if ($this->two_factor_recovery_codes === null) {
            return [];
        }

        $codes = json_decode((string)$this->two_factor_recovery_codes, true);

        return is_array($codes) ? array_values(array_map('strval', $codes)) : [];
    }

    public function recoveryCodeCount(): int
    {
        return count($this->recoveryCodes());
    }

    /**
     * HMAC-SHA256 under APP_KEY, bound to the user id. 40-bit codes would be
     * brute-forceable from a leaked plain hash; the key makes a DB leak alone
     * useless. Rotating APP_KEY invalidates every stored code (regenerate them).
     */
    public static function hashRecoveryCode(int $userId, string $code): string
    {
        $key = (string)config('app.key');
        if ($key === '') {
            throw new \RuntimeException('APP_KEY must be set to hash recovery codes.');
        }

        return self::RECOVERY_HASH_PREFIX
            . hash_hmac('sha256', 'recovery-code|' . $userId . '|' . strtoupper(trim($code)), $key);
    }

    /**
     * Replace the stored set with the hashes of these plaintext codes. The
     * caller shows the plaintext to the user once and must not persist it.
     *
     * @param array<int, string> $plain
     */
    public function storeRecoveryCodes(array $plain): void
    {
        $this->two_factor_recovery_codes = json_encode(array_map(
            fn (string $code): string => self::hashRecoveryCode((int)$this->id, $code),
            array_values($plain),
        ));
    }

    /**
     * Hash any legacy plaintext entries in memory (not saved). Returns whether
     * anything changed.
     */
    public function upgradeLegacyRecoveryCodes(): bool
    {
        $changed = false;
        $entries = array_map(function (string $entry) use (&$changed): string {
            if (str_starts_with($entry, self::RECOVERY_HASH_PREFIX)) {
                return $entry;
            }
            $changed = true;

            return self::hashRecoveryCode((int)$this->id, $entry);
        }, $this->recoveryCodes());

        if ($changed) {
            $this->two_factor_recovery_codes = json_encode($entries);
        }

        return $changed;
    }

    /**
     * Generate and return a fresh set of recovery codes without persisting them.
     *
     * @return array<int, string>
     */
    public static function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(5))); // 10 alphanumeric characters
        }

        return $codes;
    }

    /**
     * Consume one recovery code. On success the remainder is saved (legacy
     * plaintext entries upgraded to hashes in the same write) and true returned.
     * Fails closed: if the lock, reload, hashing or write fails, or two-factor
     * was disabled meanwhile, the code is refused.
     */
    public function consumeRecoveryCode(string $code): bool
    {
        $code = strtoupper(trim($code));
        $spent = false;

        $written = $this->mutateTwoFactor(function (self $user) use ($code, &$spent): void {
            if (!$user->hasTwoFactorEnabled()) {
                return;
            }
            $submitted = self::hashRecoveryCode((int)$user->id, $code);

            // Compare against every entry (no early exit) in constant time.
            $match = null;
            $entries = $user->recoveryCodes();
            foreach ($entries as $i => $entry) {
                $stored = str_starts_with($entry, self::RECOVERY_HASH_PREFIX)
                    ? $entry
                    : self::hashRecoveryCode((int)$user->id, $entry);
                if (hash_equals($stored, $submitted) && $match === null) {
                    $match = $i;
                }
            }
            if ($match === null) {
                return;
            }

            unset($entries[$match]);
            $user->two_factor_recovery_codes = json_encode(array_values($entries));
            $user->upgradeLegacyRecoveryCodes();
            $spent = true;
        });

        return $written && $spent;
    }

    /**
     * Start two-factor setup: store an unconfirmed secret and drop any earlier
     * codes (a batch is issued only when setup is confirmed).
     */
    public function beginTwoFactor(string $secret): bool
    {
        return $this->mutateTwoFactor(function (self $user) use ($secret): void {
            $user->two_factor_secret = $secret;
            $user->two_factor_recovery_codes = null;
            $user->two_factor_confirmed_at = null;
        });
    }

    /**
     * Confirm a pending setup and issue its recovery codes (also for setups
     * begun before codes were issued at confirmation). Null when there is no
     * pending secret, it is already confirmed, or the write was refused.
     *
     * @return array<int, string>|null plaintext, to be shown once
     */
    public function confirmTwoFactor(string $verifiedSecret): ?array
    {
        $codes = null;
        $written = $this->mutateTwoFactor(function (self $user) use (&$codes, $verifiedSecret): void {
            // Only confirm the secret the code was checked against, not one that
            // replaced it while waiting for the lock.
            if ($user->two_factor_secret === null || !hash_equals((string)$user->two_factor_secret, $verifiedSecret)
                || $user->two_factor_confirmed_at !== null) {
                return;
            }
            $codes = self::generateRecoveryCodes();
            $user->storeRecoveryCodes($codes);
            $user->two_factor_confirmed_at = date('Y-m-d H:i:s');
        });

        return $written ? $codes : null;
    }

    /**
     * Replace the recovery codes of an enabled account.
     *
     * @return array<int, string>|null plaintext, to be shown once
     */
    public function regenerateRecoveryCodes(): ?array
    {
        $codes = null;
        $written = $this->mutateTwoFactor(function (self $user) use (&$codes): void {
            if (!$user->hasTwoFactorEnabled()) {
                return;
            }
            $codes = self::generateRecoveryCodes();
            $user->storeRecoveryCodes($codes);
        });

        return $written ? $codes : null;
    }

    public function disableTwoFactor(): bool
    {
        return $this->mutateTwoFactor(function (self $user): void {
            $user->two_factor_secret = null;
            $user->two_factor_recovery_codes = null;
            $user->two_factor_confirmed_at = null;
        });
    }

    /** Hash legacy plaintext entries. True when something was upgraded and written. */
    public function hashLegacyRecoveryCodes(): bool
    {
        $changed = false;
        $written = $this->mutateTwoFactor(function (self $user) use (&$changed): void {
            $changed = $user->upgradeLegacyRecoveryCodes();
        });

        return $written && $changed;
    }

    /**
     * The one writer of the two-factor columns. Under a per-user lock it reloads
     * the row, lets $change edit this object, and writes only if the row is still
     * what was read (compare-and-swap in SQL, so a second host cannot overwrite a
     * newer list even though the lock is per host). Returns false, with the
     * in-memory state restored, when nothing was written.
     *
     * Failures are logged as stage + user id only: exception messages can carry
     * SQL parameters (recovery entries) and are deliberately not logged.
     *
     * @param callable(self): void $change
     */
    public function mutateTwoFactor(callable $change): bool
    {
        $backup = $this->twoFactorState();
        $stage = 'lock';

        try {
            return FileLock::run('recovery_codes_' . (int)$this->id, function () use ($change, &$stage): bool {
                $stage = 'reload';
                $this->reloadRecoveryCodes();
                $expected = $this->twoFactorState();

                $stage = 'change';
                $change($this);
                $next = $this->twoFactorState();
                if ($next === $expected) {
                    return true; // nothing to write
                }

                $stage = 'write';
                if (!$this->persistTwoFactor($expected)) {
                    throw new \RuntimeException('Two-factor state changed concurrently.');
                }

                return true;
            });
        } catch (\Throwable) {
            $this->applyTwoFactorState($backup);
            error_log('User::mutateTwoFactor: refused (stage=' . $stage . ', user=' . (int)$this->id . ')');

            return false;
        }
    }

    /** @return array{0: ?string, 1: ?string, 2: mixed} */
    private function twoFactorState(): array
    {
        $at = $this->two_factor_confirmed_at;

        return [
            $this->two_factor_secret === null ? null : (string)$this->two_factor_secret,
            $this->two_factor_recovery_codes === null ? null : (string)$this->two_factor_recovery_codes,
            $at === null ? null : ($at instanceof \DateTimeInterface ? $at->format('Y-m-d H:i:s') : (string)$at),
        ];
    }

    /** @param array{0: ?string, 1: ?string, 2: mixed} $state */
    private function applyTwoFactorState(array $state): void
    {
        [$this->two_factor_secret, $this->two_factor_recovery_codes, $this->two_factor_confirmed_at] = $state;
    }

    /**
     * Write the columns only if they still equal $expected (the values read
     * under the lock). Overridden in tests that have no database.
     *
     * @param array{0: ?string, 1: ?string, 2: mixed} $expected
     */
    protected function persistTwoFactor(array $expected): bool
    {
        $now = $this->twoFactorState();
        $db = app()->make($this->getWriteConnectionService());
        $db->execute(
            'UPDATE ' . $this->table . ' SET two_factor_secret = ?, two_factor_recovery_codes = ?, two_factor_confirmed_at = ?'
            . " WHERE id = ? AND COALESCE(two_factor_secret, '') = ? AND COALESCE(two_factor_recovery_codes, '') = ?"
            . " AND COALESCE(two_factor_confirmed_at, '') = ?",
            [$now[0], $now[1], $now[2], (int)$this->id, (string)$expected[0], (string)$expected[1], (string)$expected[2]],
        );

        return $db->affectedRows() === 1;
    }

    /**
     * Re-read the two-factor columns so a stale in-memory copy cannot be
     * written back. Skipped for unsaved models; tests override it.
     */
    protected function reloadRecoveryCodes(): void
    {
        if (!$this->id) {
            return;
        }

        $fresh = static::findFirst((int)$this->id);
        if (!$fresh instanceof self) {
            throw new \RuntimeException('Recovery codes could not be reloaded.');
        }

        $this->applyTwoFactorState($fresh->twoFactorState());
    }
}
