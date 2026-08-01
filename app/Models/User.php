<?php

namespace App\Models;

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
 *
 * Phalcon magic finders (implemented by Model::__callStatic).
 *
 * @method static static|null findFirstById(mixed $id)
 * @method static static|null findFirstByEmail(string $email)
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
        'is_admin',
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
     * The hash embedded in the verification link (Laravel compatible: sha1(email)).
     */
    public function verificationHash(): string
    {
        return sha1((string)$this->email);
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
     */
    public function consumeRecoveryCode(string $code): bool
    {
        $code = strtoupper(trim($code));
        $codes = $this->recoveryCodes();

        $index = array_search($code, $codes, true);
        if ($index === false) {
            return false;
        }

        unset($codes[$index]);
        $this->two_factor_recovery_codes = json_encode(array_values($codes));
        $this->save();

        return true;
    }
}
