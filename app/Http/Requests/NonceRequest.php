<?php

namespace App\Http\Requests;

use Framework\Http\Request;

class NonceRequest extends Request
{
    public function rules(): array
    {
        return [
            'device_id' => 'required'
        ];
    }
}