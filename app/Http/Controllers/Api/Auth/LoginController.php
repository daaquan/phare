<?php

namespace App\Http\Controllers\Api\Auth;

use App\Contracts\Repository\UserContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use Phox\Attributes\Route;
use Phox\Foundation\Http\ResponseStatusCode;

class LoginController extends Controller
{
    protected UserContract $user;

    public function onConstruct()
    {
        $this->user = app(UserContract::class);
    }

    #[Route('login', methods: ['POST'], name: 'store')]
    public function store()
    {
        $request = new LoginRequest();
        $requestData = $request->all();

        if (!$request->validate($requestData)) {
            return response($request->getMessages(), ResponseStatusCode::BAD_REQUEST);
        }

        if (!\Auth::attempt($requestData)) {
            return response(['message' => 'Unauthorized'], ResponseStatusCode::BAD_UNAUTHORIZED);
        }

        return response(['user_id' => \ID::encode(str_split(\Auth::user()->id))]);
    }

    #[Route('logout', methods: ['POST'], name: 'logout')]
    public function logout()
    {
        \Auth::logout();

        return response(['message' => 'Logged out']);
    }
}
