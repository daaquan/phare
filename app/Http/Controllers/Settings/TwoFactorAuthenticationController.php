<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Auth\AuthSession;
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

        // Issue the unconfirmed secret only; recovery codes are issued (and shown
        // once) when the setup is confirmed.
        if (!$user->beginTwoFactor(Totp::generateSecret())) {
            $this->session->set('errors', ['code' => 'Two-factor setup could not be started. Try again.']);
        }

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

        $codes = $user->confirmTwoFactor((string)$user->two_factor_secret);
        if ($codes === null) {
            $this->session->set('errors', ['code' => 'Two-factor setup could not be confirmed. Start again.']);

            return $this->response->redirect('/settings/security');
        }
        AuthSession::flashRecoveryCodes($user, $codes);

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

        $codes = $user->regenerateRecoveryCodes();
        if ($codes === null) {
            $this->session->set('errors', ['code' => 'Recovery codes could not be regenerated. Try again.']);

            return $this->response->redirect('/settings/security');
        }
        AuthSession::flashRecoveryCodes($user, $codes);

        $this->flashSession->success('Recovery codes regenerated.');

        return $this->response->redirect('/settings/security');
    }

    #[Route('two-factor', methods: ['DELETE'], middlewares: ['auth', 'verified', 'password.confirm'], name: 'settings.two-factor.disable')]
    public function disable(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->response->redirect('/user/login');
        }

        $this->session->remove(User::NEW_RECOVERY_CODES_KEY);
        if (!$user->disableTwoFactor()) {
            $this->session->set('errors', ['code' => 'Two-factor authentication could not be disabled. Try again.']);

            return $this->response->redirect('/settings/security');
        }

        $this->flashSession->success('Two-factor authentication disabled.');

        return $this->response->redirect('/settings/security');
    }
}
