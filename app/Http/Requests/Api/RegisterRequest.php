<?php

namespace App\Http\Requests\Api;

use Phare\Http\Request;

class RegisterRequest extends Request
{
    public function rules(): array
    {
        return [
            // NOTE: `unique` is not supported by the framework Validator, so it is unused.
            // Duplicate emails are caught by the unique constraint on the users table,
            // and RegisterController returns 400 when createUser fails.
            'name' => 'required|min:2',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ];
    }
}
