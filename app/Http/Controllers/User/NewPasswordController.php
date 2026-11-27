<?php

namespace App\Http\Controllers\User;

use App\Contracts\Repository\UserContract;
use App\Http\Controllers\Controller;
use App\Support\Auth\CredentialChange;
use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Support\Facades\Inertia;

class NewPasswordController extends Controller
{
    protected UserContract $users;

    public function onConstruct(): void
    {
        $this->users = app(UserContract::class);
    }

    #[Route('reset-password/{token}', middlewares: ['guest'], name: 'password.reset')]
    public function create(Request $request, string $token)
    {
        return Inertia::render('auth/ResetPassword', [
            'token' => $token,
            'email' => (string)$request->getQuery('email', 'string', ''),
        ]);
    }

    #[Route('reset-password', methods: ['POST'], middlewares: ['guest', 'throttle.password'], name: 'password.update')]
    public function store(Request $request)
    {
        $email = (string)$request->get('email');
        $token = (string)$request->get('token');
        $password = (string)$request->get('password');
        $confirmation = (string)$request->get('password_confirmation');

        if (strlen($password) < 8) {
            $this->session->set('errors', ['password' => 'The password must be at least 8 characters.']);

            return $this->response->redirect('/user/reset-password/' . $token . '?email=' . urlencode($email));
        }

        if ($password !== $confirmation) {
            $this->session->set('errors', ['password' => 'The passwords do not match.']);

            return $this->response->redirect('/user/reset-password/' . $token . '?email=' . urlencode($email));
        }

        // Verify, write and consume under the per-email lock: a concurrent request
        // with the same token waits, then finds it gone. Fail closed on lock errors.
        try {
            $result = CredentialChange::withEmailLock($email, function () use ($email, $token, $password): string {
                $broker = app('password.broker');
                if (!$broker->validateToken($email, $token)) {
                    return 'token';
                }

                $user = $this->users->getUserByEmail($email);
                if ($user === null) {
                    return 'user';
                }

                if (CredentialChange::setPassword($user, $password) === null) {
                    return 'busy';
                }

                $broker->deleteToken($email);

                return 'ok';
            });
        } catch (\RuntimeException $e) {
            $result = 'busy';
        }

        if ($result !== 'ok') {
            $this->session->set('errors', ['email' => match ($result) {
                'busy' => 'Please try again in a moment.',
                'user' => 'User not found.',
                default => 'That token is invalid or has expired.',
            }]);

            return $this->response->redirect('/user/forgot-password');
        }

        // No automatic login: signing in again goes through the normal login,
        // which enforces two-factor authentication.
        $this->flashSession->success('Your password has been reset. Please sign in.');

        return $this->response->redirect('/user/login');
    }
}
