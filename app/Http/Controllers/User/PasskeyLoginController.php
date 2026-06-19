<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\WebAuthn\WebAuthnService;
use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Support\Facades\Auth;

/**
 * パスキー（WebAuthn）でのログイン。ユーザー名なしの discoverable credential を使い、
 * アサーション検証に成功したら所有ユーザーでログインする。パスキーは強い要素のため
 * パスワード・二段階認証はスキップする。フロントエンドは fetch で呼び JSON を受け取る。
 */
class PasskeyLoginController extends Controller
{
    #[Route('passkeys/options', methods: ['POST'], middlewares: ['guest'], name: 'passkeys.login.options')]
    public function options(Request $request)
    {
        $json = (new WebAuthnService())->requestOptions();
        $this->session->set('passkey.request', $json);

        return $this->rawJson($json);
    }

    #[Route('passkeys/login', methods: ['POST'], middlewares: ['guest', 'throttle'], name: 'passkeys.login.store')]
    public function login(Request $request)
    {
        $optionsJson = (string)$this->session->get('passkey.request');
        $this->session->remove('passkey.request');
        if ($optionsJson === '') {
            return $this->json(['message' => 'セッションが切れました。やり直してください。'], 422);
        }

        $body = $request->getJsonRawBody(true) ?: [];
        $response = $body['response'] ?? null;
        if (!is_array($response)) {
            return $this->json(['message' => '不正なリクエストです。'], 422);
        }

        try {
            $user = (new WebAuthnService())->verifyAssertion($optionsJson, (string)json_encode($response));
        } catch (\Throwable $e) {
            $user = null;
        }

        if (!$user instanceof User) {
            return $this->json(['message' => 'パスキーでのログインに失敗しました。'], 422);
        }

        Auth::login($user);

        return $this->json(['ok' => true, 'redirect' => '/dashboard']);
    }
}
