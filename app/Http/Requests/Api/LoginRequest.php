<?php

namespace App\Http\Requests\Api;

use Phox\Http\Request;

class LoginRequest extends Request
{
    public function rules(): array
    {
        return [
            'id' => 'required|numeric',
            'password' => 'required'
        ];
    }
}