<?php

namespace App\Http\Requests\Web;

use Phox\Http\Request;

class LoginRequest extends Request
{
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
        ];
    }
}
