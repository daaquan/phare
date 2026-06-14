<?php

namespace App\Http\Requests\Web;

use Phare\Http\Request;

class RegisterRequest extends Request
{
    public function rules(): array
    {
        // `unique` / `confirmed` are not supported by the framework validator;
        // email uniqueness is enforced by the DB constraint and the password
        // confirmation is checked in the controller.
        return [
            'name' => 'required|min:2',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ];
    }
}
