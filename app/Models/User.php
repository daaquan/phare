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
 * @property \DateTime|string|null $email_verified_at 読み出しは datetime キャスト、代入は文字列/null も可
 * @property string $password
 * @property \DateTime $birthday
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property \DateTime|string|null $two_factor_confirmed_at
 * @property bool $is_admin
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 *
 * Phalcon のマジックファインダー（実体は Model::__callStatic）。
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
     * 管理者か（管理画面アクセス制御に使用）。
     */
    public function isAdmin(): bool
    {
        // readAttribute は未設定カラムでも例外を投げず null を返す
        // （is_admin マイグレーション未適用の環境/テストでも安全）。
        return (bool)$this->readAttribute('is_admin');
    }

    /**
     * ユーザーは複数の投稿を持つ。
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * ユーザーは複数のパスキー（WebAuthn 資格情報）を持つ。
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
     * メール認証が済んでいるか。
     */
    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null;
    }

    /**
     * メール認証済みとして記録する。
     */
    public function markEmailAsVerified(): void
    {
        $this->email_verified_at = date('Y-m-d H:i:s');
        $this->save();
    }

    /**
     * メール認証リンクに埋め込むハッシュ（Laravel 互換: sha1(email)）。
     */
    public function verificationHash(): string
    {
        return sha1((string)$this->email);
    }

    /**
     * 二段階認証が有効化（確認済み）か。
     */
    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_secret !== null
            && $this->two_factor_confirmed_at !== null;
    }

    /**
     * リカバリーコード一覧（未消費分）。
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
     * 新しいリカバリーコード一式を生成して返す（保存はしない）。
     *
     * @return array<int, string>
     */
    public static function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(5))); // 10桁の英数字
        }

        return $codes;
    }

    /**
     * リカバリーコードを 1 つ消費する。成功すれば残りを保存して true。
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
