<?php

namespace App\Http\Requests\Api;

use Phare\Http\Request;

class RegisterRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required|min:2',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ];
    }
}
