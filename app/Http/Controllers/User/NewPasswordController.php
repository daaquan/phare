<?php

namespace App\Http\Controllers\User;

use App\Contracts\Repository\UserContract;
use App\Http\Controllers\Controller;
use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Support\Facades\Auth;
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

    #[Route('reset-password', methods: ['POST'], middlewares: ['guest'], name: 'password.update')]
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

        $broker = app('password.broker');
        if (!$broker->validateToken($email, $token)) {
            $this->session->set('errors', ['email' => 'That token is invalid or has expired.']);

            return $this->response->redirect('/user/forgot-password');
        }

        $user = $this->users->getUserByEmail($email);
        if ($user === null) {
            $this->session->set('errors', ['email' => 'User not found.']);

            return $this->response->redirect('/user/forgot-password');
        }

        $user->password = $password;
        $user->save();

        $broker->deleteToken($email);
        Auth::login($user);

        return $this->response->redirect('/dashboard');
    }
}
