<?php

namespace App\Models\Game;

use Phox\Auth\Authenticable;
use Phox\Contracts\Auth\Authenticable as AuthenticableInterface;
use Phox\Eloquent\Concerns\HasTimestamps;
use Phox\Eloquent\Concerns\SoftDeletes;
use Phox\Eloquent\Model;

/**
 * @property int $id
 * @property string $device_id
 * @property string $name
 * @property string $email
 * @property \DateTime $email_verified_at
 * @property string $password
 * @property \DateTime $birthday
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 * @property \DateTime $deleted_at
 */
class User extends Model implements AuthenticableInterface
{
    use Authenticable;
    use HasTimestamps;
    use SoftDeletes;

    protected array $fillable = [
        'id',
        'device_id',
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
