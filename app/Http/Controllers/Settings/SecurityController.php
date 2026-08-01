<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Passkey;
use App\Models\User;
use App\Support\TwoFactor\Totp;
use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Support\Facades\Auth;
use Phare\Support\Facades\Inertia;

/**
 * セキュリティ設定ページ。Laravel 13 スターターキットに合わせ、パスワード変更・
 * 二段階認証・パスキーを 1 つの画面に統合する（個別の更新エンドポイントは
 * PasswordController / TwoFactorAuthenticationController / PasskeyController が担う）。
 */
class SecurityController extends Controller
{
    #[Route('security', middlewares: ['auth', 'verified'], name: 'settings.security')]
    public function show(Request $request)
    {
        $user = Auth::user();

        $enabled = $user instanceof User && $user->hasTwoFactorEnabled();
        $pending = $user instanceof User
            && $user->two_factor_secret !== null
            && !$enabled;

        $twoFactor = [
            'enabled' => $enabled,
            'pending' => $pending,
        ];

        // 未確認のシークレットがある間だけ手入力キー / otpauth URI を露出する。
        // $pending の時点で $user は User かつ two_factor_secret が非 null。
        if ($pending) {
            $secret = (string)$user->two_factor_secret;
            $twoFactor['secret'] = $secret;
            $twoFactor['otpauthUri'] = Totp::provisioningUri(
                $secret,
                (string)$user->email,
                (string)config('app.name', 'Phare'),
            );
        }

        // $enabled も同様に $user が User であることを含意する。
        if ($enabled) {
            $twoFactor['recoveryCodes'] = $user->recoveryCodes();
        }

        return Inertia::render('settings/security', [
            'twoFactor' => $twoFactor,
            'passkeys' => $this->passkeys($user),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function passkeys(?object $user): array
    {
        if (!$user instanceof User) {
            return [];
        }

        $passkeys = [];
        foreach (Passkey::findByUserId((int)$user->id) as $passkey) {
            $passkeys[] = [
                'id' => (int)$passkey->id,
                'name' => (string)$passkey->name,
                'last_used_at' => $passkey->last_used_at,
                'created_at' => $passkey->created_at,
            ];
        }

        return $passkeys;
    }
}
