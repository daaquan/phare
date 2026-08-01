<?php

namespace App\Http\Controllers\User;

use App\Contracts\Repository\UserContract;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\TwoFactor\Totp;
use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Support\Facades\Auth;
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
        if (!$this->session->has('login.id')) {
            return $this->response->redirect('/user/login');
        }

        return Inertia::render('auth/TwoFactorChallenge');
    }

    #[Route('two-factor-challenge', methods: ['POST'], middlewares: ['guest', 'throttle'], name: 'two-factor.login.store')]
    public function store(Request $request)
    {
        $userId = $this->session->get('login.id');
        if ($userId === null) {
            return $this->response->redirect('/user/login');
        }

        $user = $this->users->getUserById((int)$userId);
        if (!$user instanceof User || !$user->hasTwoFactorEnabled()) {
            $this->session->remove('login.id');

            return $this->response->redirect('/user/login');
        }

        $code = trim((string)$request->get('code'));
        $recovery = trim((string)$request->get('recovery_code'));

        $authenticated = false;
        if ($recovery !== '') {
            $authenticated = $user->consumeRecoveryCode($recovery);
        } elseif ($code !== '') {
            $authenticated = Totp::verify((string)$user->two_factor_secret, $code);
        }

        if (!$authenticated) {
            $this->session->set('errors', ['code' => 'That authentication code is not correct.']);

            return $this->response->redirect('/user/two-factor-challenge');
        }

        $this->session->remove('login.id');
        Auth::login($user);

        return $this->response->redirect('/dashboard');
    }
}
