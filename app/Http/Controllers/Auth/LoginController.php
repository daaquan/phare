<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\Repository\UserContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\LoginRequest;
use Phox\Attributes\Route;
use Phox\Http\Request;

class LoginController extends Controller
{
    public function onConstruct()
    {
        $this->user = app(UserContract::class);
    }

    #[Route('login', name: 'login')]
    public function index(Request $request)
    {
        return view('auth.login');
    }

    #[Route('login', methods: ['POST'], name: 'store')]
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
            return redirect('/');
        }

        $this->flashSession->error(__('auth.failed'));

        return redirect(route('login'));
    }
}
