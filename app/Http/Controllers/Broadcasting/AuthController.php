<?php

namespace App\Http\Controllers\Broadcasting;

use App\Http\Controllers\Controller;
use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Support\Facades\Broadcast;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * private / presence チャンネルの購読認可エンドポイント。
 * laravel-echo (pusher-js) が POST /broadcasting/auth に socket_id と
 * channel_name を送ってくる。Laravel の同名エンドポイント相当。
 */
class AuthController extends Controller
{
    #[Route('auth', methods: ['POST'], middlewares: ['auth'], name: 'broadcasting.auth')]
    public function authenticate(Request $request)
    {
        // チャンネル認可コールバックをリクエスト時に登録する。
        // ブロードキャスター(driver)はインスタンスがキャッシュされるため、
        // ここで登録 → 直後の auth() 評価で同じインスタンスが参照される。
        // ブート順依存を避けるための lazy 登録。
        require base_path('routes/channels.php');

        try {
            $result = Broadcast::auth($request);
        } catch (AccessDeniedHttpException) {
            return $this->json(['message' => 'Forbidden'], 403);
        }

        // pusher ドライバは署名済み JSON 文字列を返す。bool/配列はラップ済み。
        return $this->rawJson(is_string($result) ? $result : (string)json_encode($result));
    }
}
