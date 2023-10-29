<?php

namespace App\Http\Requests;

use Framework\Http\Request;

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