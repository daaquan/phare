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
 * @property \DateTime $email_verified_at
 * @property string $password
 * @property \DateTime $birthday
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
    ];

    protected array $hidden = [
        'password',
    ];

    protected array $passwordAttributes = [
        'password',
    ];

    protected array $casts = [
        'email_verified_at' => 'datetime',
        'birthday' => 'date',
    ];

    /**
     * A user has many posts.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
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
}
