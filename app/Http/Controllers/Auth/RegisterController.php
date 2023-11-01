<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\Repository\UserContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\NonceRequest;
use App\Http\Requests\RegisterRequest;
use Phox\Foundation\Http\ResponseStatusCode;

class RegisterController extends Controller
{
    private UserContract $user;

    public function onConstruct()
    {
        $this->user = app(UserContract::class);
    }

    public function nonce()
    {
        $request = new NonceRequest();
        if (!$request->validate($request->all())) {
            return abort($request->getMessages());
        }

        $device_id = $request->input('device_id');
        $nonce = $this->user->newNonce($device_id);
        return response(compact('nonce'));
    }

    public function store()
    {
        $request = new RegisterRequest();
        if (!$request->validate($data = $request->all())) {
            return response($request->getMessages(), ResponseStatusCode::BAD_REQUEST);
        }

        $user = $this->user->createUser($data);

        return response($user?->toArray());
    }
}
