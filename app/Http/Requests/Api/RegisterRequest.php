<?php

namespace App\Http\Requests\Api;

use Phare\Http\Request;

class RegisterRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required',
        ];
    }
}
