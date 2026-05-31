<?php

namespace App\Http\Controllers\Api\Auth;

use App\Contracts\Repository\UserContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RegisterRequest;
use Phare\Attributes\Route;
use Phare\Foundation\Http\ResponseStatusCode;

class RegisterController extends Controller
{
    protected UserContract $users;

    public function onConstruct(): void
    {
        $this->users = app(UserContract::class);
    }

    #[Route('register', methods: ['POST'], middlewares: ['throttle'], name: 'register')]
    public function store()
    {
        $request = new RegisterRequest();
        $payload = $request->all();

        if (!$request->validate($payload)) {
            return response($request->getMessages(), ResponseStatusCode::UNPROCESSABLE_ENTITY);
        }

        try {
            $user = $this->users->createUser($request->only(['name', 'email', 'password']));
        } catch (\Throwable $e) {
            return response(['message' => 'Unable to create user'], ResponseStatusCode::BAD_REQUEST);
        }

        return response([
            'id' => \ID::encode($user->id),
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }
}
