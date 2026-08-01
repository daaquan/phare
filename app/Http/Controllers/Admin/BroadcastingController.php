<?php

namespace App\Http\Controllers\Admin;

use App\Events\MessageBroadcast;
use App\Http\Controllers\Controller;
use App\Models\User;
use Phare\Attributes\Route;
use Phare\Broadcasting\Broadcasters\PusherBroadcaster;
use Phare\Http\Request;
use Phare\Support\Facades\Auth;
use Phare\Support\Facades\Broadcast;
use Phare\Support\Facades\Inertia;
use Phare\Support\Facades\Log;
use Pusher\Pusher;

/**
 * Broadcasting 監視ダッシュボード。Soketi (Pusher プロトコル) の HTTP API を
 * Pusher SDK 経由で叩き、占有チャンネル / 購読数 / presence メンバーを返す。
 *
 * Soketi 未起動でもダッシュボードは落とさない（空 + 警告ログ）。
 */
class BroadcastingController extends Controller
{
    #[Route('broadcasting', middlewares: ['auth', 'verified', 'admin'], name: 'admin.broadcasting')]
    public function index(Request $request)
    {
        return Inertia::render('admin/Broadcasting', [
            'connection' => $this->connectionMeta(),
        ]);
    }

    /**
     * 占有中チャンネル一覧（購読数つき）。
     */
    #[Route('broadcasting/channels', middlewares: ['auth', 'verified', 'admin'], name: 'admin.broadcasting.channels')]
    public function channels(Request $request)
    {
        $prefix = (string)($request->get('prefix') ?? '');

        try {
            $pusher = $this->pusher();
            $params = ['info' => 'subscription_count'];
            if ($prefix !== '') {
                $params['filter_by_prefix'] = $prefix;
            }
            $result = $pusher->getChannels($params);

            $channels = [];
            foreach ((array)($result->channels ?? []) as $name => $info) {
                $channels[] = [
                    'name' => $name,
                    'type' => $this->channelType($name),
                    'subscription_count' => $info->subscription_count ?? null,
                ];
            }

            return $this->json(['ok' => true, 'channels' => $channels]);
        } catch (\Throwable $e) {
            Log::warning('Broadcasting monitor: getChannels 失敗', ['error' => $e->getMessage()]);

            return $this->json(['ok' => false, 'channels' => [], 'error' => $e->getMessage()]);
        }
    }

    /**
     * 単一チャンネルの詳細（占有・購読数、presence ならメンバー）。
     * チャンネル名はドットを含むためクエリ ?name= で受ける。
     */
    #[Route('broadcasting/channel', middlewares: ['auth', 'verified', 'admin'], name: 'admin.broadcasting.channel')]
    public function channel(Request $request)
    {
        $name = (string)($request->get('name') ?? '');
        if ($name === '') {
            return $this->json(['ok' => false, 'error' => 'name required'], 422);
        }

        try {
            $pusher = $this->pusher();

            $info = $pusher->getChannelInfo($name, ['info' => 'subscription_count,user_count']);
            $payload = [
                'name' => $name,
                'type' => $this->channelType($name),
                'occupied' => $info->occupied ?? false,
                'subscription_count' => $info->subscription_count ?? null,
                'user_count' => $info->user_count ?? null,
                'members' => [],
            ];

            if (str_starts_with($name, 'presence-')) {
                $users = $pusher->getPresenceUsers($name);
                $payload['members'] = array_map(
                    fn ($u) => ['id' => $u->id ?? null],
                    (array)($users->users ?? [])
                );
            }

            return $this->json(['ok' => true, 'channel' => $payload]);
        } catch (\Throwable $e) {
            Log::warning('Broadcasting monitor: getChannelInfo 失敗', ['error' => $e->getMessage()]);

            return $this->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * テスト送信。現在の管理者の private チャンネルと presence-monitor に流す。
     */
    #[Route('broadcasting/test', methods: ['POST'], middlewares: ['auth', 'verified', 'admin'], name: 'admin.broadcasting.test')]
    public function test(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->json(['ok' => false, 'error' => 'Unauthorized'], 401);
        }

        $message = trim((string)($request->get('message') ?? 'test broadcast'));

        try {
            broadcast(new MessageBroadcast((int)$user->id, $message))->send();

            return $this->json(['ok' => true]);
        } catch (\Throwable $e) {
            Log::warning('Broadcasting monitor: test send 失敗', ['error' => $e->getMessage()]);

            return $this->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * 監視 API は Soketi (Pusher プロトコル) の HTTP クライアント前提。
     * 他ドライバ構成では例外にして、呼び出し側の catch で ok:false を返す。
     */
    private function pusher(): Pusher
    {
        $driver = Broadcast::driver('pusher');

        if (!$driver instanceof PusherBroadcaster) {
            throw new \RuntimeException('broadcasting: pusher ドライバが構成されていません');
        }

        return $driver->getPusher();
    }

    private function channelType(string $name): string
    {
        if (str_starts_with($name, 'presence-')) {
            return 'presence';
        }
        if (str_starts_with($name, 'private-')) {
            return 'private';
        }

        return 'public';
    }

    /**
     * フロントへ渡す接続メタ（鍵は公開鍵のみ）。
     *
     * @return array<string, mixed>
     */
    private function connectionMeta(): array
    {
        $config = app('config')->get('broadcasting.connections.pusher', []);

        return [
            'driver' => app('config')->get('broadcasting.default'),
            'host' => $config['host'] ?? null,
            'port' => $config['port'] ?? null,
            'scheme' => $config['scheme'] ?? null,
        ];
    }
}
