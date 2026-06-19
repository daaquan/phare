<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Passkey;
use App\Models\User;
use App\Support\WebAuthn\WebAuthnService;
use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Support\Facades\Auth;

/**
 * パスキー（WebAuthn 資格情報）の登録・削除。フロントエンドは fetch で呼び、
 * 登録レスポンスやエラーを JSON で受け取る（Inertia ページ遷移ではない）。
 *
 * ponytail: Laravel は password.confirm でも保護するが、ここは
 * auth + verified + 登録時の WebAuthn ユーザー認証ジェスチャ（生体/PIN）を
 * 再認証とみなして簡素化している。より厳格にするなら password.confirm を追加。
 */
class PasskeyController extends Controller
{
    #[Route('passkeys/options', methods: ['POST'], middlewares: ['auth', 'verified'], name: 'settings.passkeys.options')]
    public function options(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        $json = (new WebAuthnService())->creationOptions($user);
        $this->session->set('passkey.creation', $json);

        return $this->rawJson($json);
    }

    #[Route('passkeys', methods: ['POST'], middlewares: ['auth', 'verified'], name: 'settings.passkeys.store')]
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        $optionsJson = (string)$this->session->get('passkey.creation');
        $this->session->remove('passkey.creation');
        if ($optionsJson === '') {
            return $this->json(['message' => 'セッションが切れました。やり直してください。'], 422);
        }

        $body = $request->getJsonRawBody(true) ?: [];
        $response = $body['response'] ?? null;
        if (!is_array($response)) {
            return $this->json(['message' => '不正なリクエストです。'], 422);
        }

        try {
            (new WebAuthnService())->verifyAndStore(
                $user,
                $optionsJson,
                (string)json_encode($response),
                trim((string)($body['name'] ?? '')),
            );
        } catch (\Throwable $e) {
            return $this->json(['message' => 'パスキーの登録に失敗しました。'], 422);
        }

        $this->flashSession->success('パスキーを登録しました。');

        return $this->json(['ok' => true]);
    }

    #[Route('passkeys/{id}', methods: ['DELETE'], middlewares: ['auth', 'verified'], name: 'settings.passkeys.destroy')]
    public function destroy(Request $request, string $id)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        $passkey = Passkey::findFirst([
            'conditions' => 'id = :id: AND user_id = :user:',
            'bind' => ['id' => (int)$id, 'user' => (int)$user->id],
        ]);

        if ($passkey instanceof Passkey) {
            $passkey->delete();
            $this->flashSession->success('パスキーを削除しました。');
        }

        return $this->json(['ok' => true]);
    }
}
