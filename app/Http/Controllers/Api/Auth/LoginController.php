<?php

namespace App\Http\Controllers\Api\Auth;

use App\Contracts\Repository\UserContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Models\User;
use App\Support\Auth\AuthSession;
use Phare\Attributes\Route;
use Phare\Foundation\Http\ResponseStatusCode;

class LoginController extends Controller
{
    protected UserContract $user;

    public function onConstruct()
    {
        $this->user = app(UserContract::class);
    }

    #[Route('login', methods: ['POST'], middlewares: ['throttle.password'], name: 'store')]
    public function store()
    {
        $request = new LoginRequest();
        $requestData = $request->all();

        if (!$request->validate($requestData)) {
            return response($request->getMessages(), ResponseStatusCode::UNPROCESSABLE_ENTITY);
        }

        $credentials = $request->only(['email', 'password']);

        // Check the password without starting a session: a correct password
        // alone must not sign in an account that has two-factor enabled.
        if (!\Auth::validate($credentials)) {
            return response(['message' => 'Unauthorized'], ResponseStatusCode::BAD_UNAUTHORIZED);
        }

        $user = $this->user->getUserByEmail((string)$credentials['email']);
        if (!$user instanceof User) {
            return response(['message' => 'Unauthorized'], ResponseStatusCode::BAD_UNAUTHORIZED);
        }

        if ($user->hasTwoFactorEnabled()) {
            // ponytail: no API two-factor step yet; these accounts sign in
            // through the web login, which runs the challenge.
            return response([
                'message' => 'Two-factor authentication required. Sign in through the web login.',
                'two_factor' => true,
            ], ResponseStatusCode::FORBIDDEN);
        }

        AuthSession::login($user);

        return response(['user_id' => \ID::encode($user->id)]);
    }

    #[Route('logout', methods: ['POST'], name: 'logout')]
    public function logout()
    {
        AuthSession::logout();

        return response(['message' => 'Logged out']);
    }
}
