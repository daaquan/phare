<?php

namespace App\Http\Controllers\Api\Auth;

use App\Contracts\Repository\UserContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\NonceRequest;
use Phox\Foundation\Http\ResponseStatusCode;

class LoginController extends Controller
{
    private UserContract $user;

    public function onConstruct()
    {
        $this->user = app(UserContract::class);
    }

    public function nonce()
    {
        $request = new NonceRequest();
        $requestData = $request->all();

        if (!$request->validate($requestData)) {
            return response($request->getMessages(), ResponseStatusCode::BAD_REQUEST);
        }

        $deviceId = $requestData['device_id'];
        $nonce = $this->user->newNonce();

        \Session::put("nonce.{$deviceId}", $nonce);
        return response(compact('nonce'));
    }

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
}
