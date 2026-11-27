<?php

namespace App\Http\Controllers\User;

use App\Contracts\Repository\UserContract;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Auth\AuthSession;
use App\Support\TwoFactor\UsedTotpSteps;
use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Support\Facades\Inertia;

/**
 * Laravel parity: the two-factor challenge shown after password authentication.
 * The login is held in the `login.id` session entry and only completes once a TOTP
 * code or a recovery code verifies.
 */
class TwoFactorChallengeController extends Controller
{
    protected UserContract $users;

    public function onConstruct(): void
    {
        $this->users = app(UserContract::class);
    }

    #[Route('two-factor-challenge', middlewares: ['guest'], name: 'two-factor.login')]
    public function show(Request $request)
    {
        if (AuthSession::pendingTwoFactorId() === null) {
            return $this->response->redirect('/user/login');
        }

        return Inertia::render('auth/TwoFactorChallenge');
    }

    #[Route('two-factor-challenge', methods: ['POST'], middlewares: ['guest', 'throttle.two-factor'], name: 'two-factor.login.store')]
    public function store(Request $request)
    {
        $userId = AuthSession::pendingTwoFactorId();
        if ($userId === null) {
            return $this->response->redirect('/user/login');
        }

        $user = $this->users->getUserById((int)$userId);
        if (!$user instanceof User || !$user->hasTwoFactorEnabled()) {
            $this->session->remove('login.id');
            $this->session->remove('login.at');

            return $this->response->redirect('/user/login');
        }

        $code = trim((string)$request->get('code'));
        $recovery = trim((string)$request->get('recovery_code'));

        $authenticated = false;
        if ($recovery !== '') {
            $authenticated = $user->consumeRecoveryCode($recovery);
        } elseif ($code !== '') {
            $authenticated = UsedTotpSteps::verifyAndClaim((int)$user->id, (string)$user->two_factor_secret, $code);
        }

        if (!$authenticated) {
            $this->session->set('errors', ['code' => 'That authentication code is not correct.']);

            return $this->response->redirect('/user/two-factor-challenge');
        }

        $this->session->remove('login.id');
        $this->session->remove('login.at');
        AuthSession::login($user);

        return $this->response->redirect('/dashboard');
    }
}
