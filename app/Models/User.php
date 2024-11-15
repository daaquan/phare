<?php

namespace App\Models;

use Phox\Auth\Authenticatable;
use Phox\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Phox\Eloquent\Concerns\HasTimestamps;
use Phox\Eloquent\Concerns\SoftDeletes;
use Phox\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \DateTime $email_verified_at
 * @property string $password
 * @property \DateTime $birthday
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 * @property \DateTime $deleted_at
 */
class User extends Model implements AuthenticatableContract
{
    use Authenticatable;
    use HasTimestamps;
    use SoftDeletes;

    protected ?string $table = 'users';

    protected array $fillable = [
        'id',
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
}
