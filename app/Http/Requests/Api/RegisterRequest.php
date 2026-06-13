<?php

namespace App\Http\Requests\Api;

use Phare\Http\Request;

class RegisterRequest extends Request
{
    public function rules(): array
    {
        return [
            // NOTE: `unique` はフレームワークの Validator が未対応のため使用しない。
            // メールの重複は users テーブルの unique 制約で担保し、
            // createUser 失敗時に RegisterController が 400 を返す。
            'name' => 'required|min:2',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ];
    }
}
