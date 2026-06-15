<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\TwoFactor\Totp;
use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Support\Facades\Auth;
use Phare\Support\Facades\Inertia;

/**
 * Laravel パリティ: 二段階認証 (TOTP) の有効化・確認・無効化・
 * リカバリーコード再生成。機密操作のため `password.confirm` で保護する。
 */
class TwoFactorAuthenticationController extends Controller
{
    #[Route('two-factor', middlewares: ['auth', 'verified'], name: 'settings.two-factor')]
    public function show(Request $request)
    {
        // `auth` ミドルウェアで保護されるため、ここでは null を未設定状態として扱う。
        $user = Auth::user();
        $enabled = $user instanceof User && $user->hasTwoFactorEnabled();
        $pending = $user instanceof User
            && $user->two_factor_secret !== null
            && !$enabled;

        $props = [
            'enabled' => $enabled,
            'pending' => $pending,
        ];

        // 未確認のシークレットがある間だけ QR / 手入力キーを露出する。
        if ($pending && $user instanceof User && $user->two_factor_secret !== null) {
            $secret = (string)$user->two_factor_secret;
            $props['secret'] = $secret;
            $props['otpauthUri'] = Totp::provisioningUri(
                $secret,
                (string)$user->email,
                (string)config('app.name', 'Phare'),
            );
        }

        if ($enabled && $user instanceof User) {
            $props['recoveryCodes'] = $user->recoveryCodes();
        }

        return Inertia::render('settings/TwoFactor', $props);
    }

    #[Route('two-factor/enable', methods: ['POST'], middlewares: ['auth', 'verified', 'password.confirm'], name: 'settings.two-factor.enable')]
    public function enable(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->response->redirect('/user/login');
        }

        // 確認前のシークレットとリカバリーコードを発行（まだ confirmed にしない）。
        $user->two_factor_secret = Totp::generateSecret();
        $user->two_factor_recovery_codes = json_encode(User::generateRecoveryCodes());
        $user->two_factor_confirmed_at = null;
        $user->save();

        return $this->response->redirect('/settings/two-factor');
    }

    #[Route('two-factor/confirm', methods: ['POST'], middlewares: ['auth', 'verified', 'password.confirm'], name: 'settings.two-factor.confirm')]
    public function confirm(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->response->redirect('/user/login');
        }

        if ($user->two_factor_secret === null) {
            return $this->response->redirect('/settings/two-factor');
        }

        $code = (string)$request->get('code');
        if (!Totp::verify((string)$user->two_factor_secret, $code)) {
            $this->session->set('errors', ['code' => '認証コードが正しくありません。']);

            return $this->response->redirect('/settings/two-factor');
        }

        $user->two_factor_confirmed_at = date('Y-m-d H:i:s');
        $user->save();

        $this->flashSession->success('二段階認証を有効にしました。');

        return $this->response->redirect('/settings/two-factor');
    }

    #[Route('two-factor/recovery-codes', methods: ['POST'], middlewares: ['auth', 'verified', 'password.confirm'], name: 'settings.two-factor.recovery')]
    public function regenerateRecoveryCodes(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->response->redirect('/user/login');
        }

        $user->two_factor_recovery_codes = json_encode(User::generateRecoveryCodes());
        $user->save();

        $this->flashSession->success('リカバリーコードを再生成しました。');

        return $this->response->redirect('/settings/two-factor');
    }

    #[Route('two-factor', methods: ['DELETE'], middlewares: ['auth', 'verified', 'password.confirm'], name: 'settings.two-factor.disable')]
    public function disable(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->response->redirect('/user/login');
        }

        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        $this->flashSession->success('二段階認証を無効にしました。');

        return $this->response->redirect('/settings/two-factor');
    }
}
