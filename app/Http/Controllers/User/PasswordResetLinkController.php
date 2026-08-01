<?php

namespace App\Http\Controllers\User;

use App\Contracts\Repository\UserContract;
use App\Http\Controllers\Concerns\SendsAuthEmails;
use App\Http\Controllers\Controller;
use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Support\Facades\Inertia;

class PasswordResetLinkController extends Controller
{
    use SendsAuthEmails;

    protected UserContract $users;

    public function onConstruct(): void
    {
        $this->users = app(UserContract::class);
    }

    #[Route('forgot-password', middlewares: ['guest'], name: 'password.request')]
    public function create(Request $request)
    {
        return Inertia::render('auth/ForgotPassword');
    }

    #[Route('forgot-password', methods: ['POST'], middlewares: ['guest', 'throttle'], name: 'password.email')]
    public function store(Request $request)
    {
        $email = (string)$request->get('email');

        // Send the link only when the account exists, but always flash the same
        // status to avoid leaking which emails are registered.
        $user = $email !== '' ? $this->users->getUserByEmail($email) : null;
        if ($user !== null) {
            $token = app('password.broker')->createToken($user);
            $this->sendPasswordResetEmail($email, $token);
        }

        $this->flashSession->success('A password reset link has been sent.');

        return $this->response->redirect('/user/forgot-password');
    }
}
