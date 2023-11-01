<?php

namespace App\Http\Requests;

use Phox\Http\Request;

class RegisterRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required',
            'nonce' => 'required'
        ];
    }
}