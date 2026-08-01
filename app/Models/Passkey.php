<?php

namespace App\Models;

use Phare\Eloquent\Concerns\HasTimestamps;
use Phare\Eloquent\Model;

/**
 * A WebAuthn passkey (public key credential). The state needed for signature
 * verification lives in `data`, the CredentialRecord serialised by
 * web-auth/webauthn-lib; `credential_id` (base64url) is the lookup index column.
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $credential_id
 * @property string $data
 * @property \DateTime|string|null $last_used_at Reads as datetime; assignment also accepts a string
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 *
 * Phalcon magic finders (implemented by Model::__callStatic).
 *
 * @method static iterable<int, static> findByUserId(mixed $userId)
 * @method static static|null findFirstByCredentialId(string $credentialId)
 */
class Passkey extends Model
{
    use HasTimestamps;

    protected ?string $table = 'passkeys';

    protected array $fillable = [
        'user_id',
        'name',
        'credential_id',
        'data',
        'last_used_at',
    ];

    protected array $casts = [
        'id' => 'int',
        'user_id' => 'int',
    ];

    /**
     * A passkey belongs to one user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
