<?php

namespace App\Http\Requests\Api;

use Phox\Http\Request;

class NonceRequest extends Request
{
    public function rules(): array
    {
        return [
            'device_id' => 'required',
        ];
    }
}
