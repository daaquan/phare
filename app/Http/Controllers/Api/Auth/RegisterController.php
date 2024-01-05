<?php

namespace App\Http\Controllers\Api\Auth;

use App\Contracts\Repository\UserContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\NonceRequest;
use App\Http\Requests\Api\RegisterRequest;
use Phox\Foundation\Http\ResponseStatusCode;

class RegisterController extends Controller
{
    protected UserContract $user;

    public function __construct()
    {
        $this->user = app(UserContract::class);
    }

    public function nonce()
    {
        $request = new NonceRequest();
        if (!$request->validate($request->all())) {
            return abort($request->getMessages());
        }

        $deviceId = $request->input('device_id');
        $nonce = $this->user->newNonce();

        \Session::put("nonce.{$deviceId}", $nonce);

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
