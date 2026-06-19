<?php

namespace App\Models;

use Phare\Eloquent\Concerns\HasTimestamps;
use Phare\Eloquent\Model;

/**
 * WebAuthn パスキー（公開鍵資格情報）。署名検証に必要な状態は
 * web-auth/webauthn-lib がシリアライズした `data`（CredentialRecord）が保持し、
 * `credential_id`（base64url）は照合用のインデックス列として持つ。
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $credential_id
 * @property string $data
 * @property \DateTime $last_used_at
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
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
     * パスキーは 1 人のユーザーに属する。
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
