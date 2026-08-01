<?php

use Phare\Broadcasting\AccessDeniedException;
use Phare\Broadcasting\Broadcasters\PusherBroadcaster;

/**
 * Regression test for the subscription denial path. It used to throw Symfony's
 * AccessDeniedHttpException, a class no installed package provides, so a denial
 * died with "class not found" instead of returning 403.
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

test('an auth request for an unguarded channel raises AccessDeniedException', function () {
    $broadcaster = new PusherBroadcaster('key', 'secret', 'app-id');

    expect(fn () => $broadcaster->auth(broadcastAuthRequest([
        'channel_name' => 'public-room',
        'socket_id' => '1.1',
    ])))->toThrow(AccessDeniedException::class);
});

test('an auth request without a channel name raises AccessDeniedException', function () {
    $broadcaster = new PusherBroadcaster('key', 'secret', 'app-id');

    expect(fn () => $broadcaster->auth(broadcastAuthRequest(['socket_id' => '1.1'])))
        ->toThrow(AccessDeniedException::class);
});
