<?php

use App\Events\MessageBroadcast;
use Phare\Broadcasting\PresenceChannel;
use Phare\Broadcasting\PrivateChannel;

test('broadcastOn targets the user private channel and presence monitor', function () {
    $event = new MessageBroadcast(42, 'hello');
    $channels = $event->broadcastOn();

    expect($channels)->toHaveCount(2);
    expect($channels[0])->toBeInstanceOf(PrivateChannel::class);
    expect((string)$channels[0])->toBe('private-App.User.42');
    expect($channels[1])->toBeInstanceOf(PresenceChannel::class);
    expect((string)$channels[1])->toBe('presence-monitor');
});

test('broadcastAs and payload expose the message', function () {
    $event = new MessageBroadcast(7, 'ping');

    expect($event->broadcastAs())->toBe('message');
    expect($event->broadcastWith())
        ->toMatchArray(['user_id' => 7, 'message' => 'ping'])
        ->toHaveKey('at');
});

test('defaults to the pusher connection', function () {
    expect((new MessageBroadcast(1, 'x'))->broadcastVia())->toBe(['pusher']);
});
