<?php

namespace App\Http\Controllers\User;

use App\Contracts\Repository\UserContract;
use App\Http\Controllers\Concerns\SendsAuthEmails;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\RegisterRequest;
use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Support\Facades\Auth;
use Phare\Support\Facades\Inertia;

class RegisterController extends Controller
{
    use SendsAuthEmails;

    protected UserContract $users;

    public function onConstruct(): void
    {
        $this->users = app(UserContract::class);
    }

    #[Route('register', middlewares: ['guest'], name: 'register')]
    public function create(Request $request)
    {
        return Inertia::render('auth/Register');
    }

    #[Route('register', methods: ['POST'], middlewares: ['guest', 'throttle'], name: 'register.store')]
    public function store()
    {
        $request = new RegisterRequest();
        $data = $request->all();

        if (!$request->validate($data)) {
            return $this->backWithErrors($request, '/user/register');
        }

        if (($data['password'] ?? null) !== ($data['password_confirmation'] ?? null)) {
            $this->session->set('errors', ['password' => 'The passwords do not match.']);

            return $this->response->redirect('/user/register');
        }

        try {
            $user = $this->users->createUser($request->only(['name', 'email', 'password']));
        } catch (\Throwable $e) {
            $this->session->set('errors', ['email' => 'That email address is already taken.']);

            return $this->response->redirect('/user/register');
        }

        $this->sendVerificationEmail($user);
        Auth::login($user);

        return $this->response->redirect('/user/verify-email');
    }
}
