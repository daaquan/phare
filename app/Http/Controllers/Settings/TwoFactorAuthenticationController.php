<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\TwoFactor\Totp;
use App\Support\TwoFactor\UsedTotpSteps;
use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Support\Facades\Auth;

/**
 * Laravel parity: enable, confirm, disable and regenerate recovery codes for
 * two-factor (TOTP). SecurityController (/settings/security) renders the screen;
 * this class only serves the write endpoints, guarded by password.confirm.
 */
class TwoFactorAuthenticationController extends Controller
{
    #[Route('two-factor/enable', methods: ['POST'], middlewares: ['auth', 'verified', 'password.confirm'], name: 'settings.two-factor.enable')]
    public function enable(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->response->redirect('/user/login');
        }

        // Issue the unconfirmed secret and recovery codes (not confirmed yet).
        $user->two_factor_secret = Totp::generateSecret();
        $codes = User::generateRecoveryCodes();
        $user->storeRecoveryCodes($codes);
        $user->two_factor_confirmed_at = null;
        $user->save();
        $this->flashNewRecoveryCodes($codes);

        return $this->response->redirect('/settings/security');
    }

    #[Route('two-factor/confirm', methods: ['POST'], middlewares: ['auth', 'verified', 'password.confirm'], name: 'settings.two-factor.confirm')]
    public function confirm(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->response->redirect('/user/login');
        }

        if ($user->two_factor_secret === null) {
            return $this->response->redirect('/settings/security');
        }

        $code = (string)$request->get('code');
        if (!UsedTotpSteps::verifyAndClaim((int)$user->id, (string)$user->two_factor_secret, $code)) {
            $this->session->set('errors', ['code' => 'That authentication code is not correct.']);

            return $this->response->redirect('/settings/security');
        }

        $user->two_factor_confirmed_at = date('Y-m-d H:i:s');
        $user->save();

        $this->flashSession->success('Two-factor authentication enabled.');

        return $this->response->redirect('/settings/security');
    }

    #[Route('two-factor/recovery-codes', methods: ['POST'], middlewares: ['auth', 'verified', 'password.confirm'], name: 'settings.two-factor.recovery')]
    public function regenerateRecoveryCodes(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->response->redirect('/user/login');
        }

        $codes = User::generateRecoveryCodes();
        $user->storeRecoveryCodes($codes);
        $user->save();
        $this->flashNewRecoveryCodes($codes);

        $this->flashSession->success('Recovery codes regenerated.');

        return $this->response->redirect('/settings/security');
    }

    /**
     * Recovery codes are stored as hashes, so the plaintext exists only here:
     * flashed for the very next security page render, then gone.
     *
     * @param array<int, string> $codes
     */
    private function flashNewRecoveryCodes(array $codes): void
    {
        $this->session->set(User::NEW_RECOVERY_CODES_KEY, $codes);
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
        $this->session->remove(User::NEW_RECOVERY_CODES_KEY);

        $this->flashSession->success('Two-factor authentication disabled.');

        return $this->response->redirect('/settings/security');
    }
}
