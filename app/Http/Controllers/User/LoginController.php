<?php

namespace App\Http\Controllers\User;

use App\Contracts\Repository\UserContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\LoginRequest;
use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Support\Facades\Inertia;

class LoginController extends Controller
{
    protected UserContract $user;

    public function onConstruct()
    {
        $this->user = app(UserContract::class);
    }

    #[Route('login', name: 'login')]
    public function index(Request $request)
    {
        return Inertia::render('auth/Login', [
            'strings' => [
                'title' => __('labels.frontend.auth.login_box_title'),
                'email' => __('labels.frontend.auth.email'),
                'password' => __('labels.frontend.auth.password'),
                'remember' => __('labels.frontend.auth.remember_me'),
                'submit' => __('labels.frontend.auth.submit'),
            ],
        ]);
    }

    #[Route('login', methods: ['POST'], middlewares: ['throttle'], name: 'store')]
    public function store(LoginRequest $request)
    {
        $formData = $request->only(['email', 'password']);

        $user = $this->user->getUserByEmail($formData['email']);
        if (!$user) {
            $this->flashSession->error(__('auth.failed'));

            return redirect(route('login'));
        }

        $credentials = [
            'id' => $user->id,
            'password' => $formData['password'],
        ];

        if (\Auth::attempt($credentials)) {
            // With two-factor enabled, hold the login until the code is confirmed.
            if ($user->hasTwoFactorEnabled()) {
                \Auth::logout();
                $this->session->set('login.id', $user->id);

                return redirect('/user/two-factor-challenge');
            }

            return redirect('/');
        }

        $this->flashSession->error(__('auth.failed'));

        return redirect(route('login'));
    }

    #[Route('logout', methods: ['POST'], name: 'logout')]
    public function logout()
    {
        \Auth::logout();

        return redirect(route('welcome'));
    }
}
