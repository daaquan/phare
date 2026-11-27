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

    /**
     * The unused recovery codes.
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
     * Consume one recovery code. On success the remainder is saved and true returned.
     *
     * The read-modify-write runs under a per-user lock on freshly reloaded
     * state, so two concurrent requests cannot both spend the same code.
     * Fails closed: if the lock, reload or save fails the code is refused
     * (and logged) and the in-memory list is left untouched.
     */
    public function consumeRecoveryCode(string $code): bool
    {
        $code = strtoupper(trim($code));
        $before = $this->two_factor_recovery_codes;

        try {
            return FileLock::run('recovery_codes_' . (int)$this->id, function () use ($code): bool {
                $this->reloadRecoveryCodes();
                $codes = $this->recoveryCodes();

                $index = array_search($code, $codes, true);
                if ($index === false) {
                    return false;
                }

                unset($codes[$index]);
                $this->two_factor_recovery_codes = json_encode(array_values($codes));
                if ($this->save() === false) {
                    throw new \RuntimeException('Recovery codes could not be saved.');
                }

                return true;
            });
        } catch (\Throwable $e) {
            $this->two_factor_recovery_codes = $before;
            error_log('User::consumeRecoveryCode: refusing code: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Re-read the stored code list so a stale in-memory copy cannot be spent
     * again. Skipped for unsaved models; tests override it.
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

        $this->two_factor_recovery_codes = $fresh->two_factor_recovery_codes;
    }
}
