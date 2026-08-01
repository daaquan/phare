<?php

use Phare\Broadcasting\AccessDeniedException;
use Phare\Broadcasting\Broadcasters\PusherBroadcaster;

/**
 * 購読認可の拒否パスの回帰テスト。以前は存在しない Symfony の
 * AccessDeniedHttpException を throw していたため、拒否時に 403 ではなく
 * "class not found" で落ちていた。
 */
function broadcastAuthRequest(array $params): object
{
    return new class($params)
    {
        public function __construct(private array $params) {}

        public function get(string $key): mixed
        {
            return $this->params[$key] ?? null;
        }
    };
}

test('guarded でないチャンネルの認可要求は AccessDeniedException になる', function () {
    $broadcaster = new PusherBroadcaster('key', 'secret', 'app-id');

    expect(fn () => $broadcaster->auth(broadcastAuthRequest([
        'channel_name' => 'public-room',
        'socket_id' => '1.1',
    ])))->toThrow(AccessDeniedException::class);
});

test('チャンネル名なしの認可要求は AccessDeniedException になる', function () {
    $broadcaster = new PusherBroadcaster('key', 'secret', 'app-id');

    expect(fn () => $broadcaster->auth(broadcastAuthRequest(['socket_id' => '1.1'])))
        ->toThrow(AccessDeniedException::class);
});
