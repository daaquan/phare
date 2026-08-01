<?php

namespace App\Events;

use Phare\Broadcasting\BroadcastEvent;
use Phare\Broadcasting\PresenceChannel;
use Phare\Broadcasting\PrivateChannel;

/**
 * Demo event for smoke-testing the broadcasting stack. Dispatched by the monitor's
 * test-send button and by in-app message notifications.
 *
 * Usage: broadcast(new MessageBroadcast($userId, 'hello'))->send();
 */
class MessageBroadcast extends BroadcastEvent
{
    public function __construct(
        public int $userId,
        public string $message,
    ) {}

    /**
     * Sent to private-App.User.{id} (the user) and presence-monitor (the dashboard).
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("App.User.{$this->userId}"),
            new PresenceChannel('monitor'),
        ];
    }

    public function broadcastAs(): ?string
    {
        return 'message';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'message' => $this->message,
            'at' => date(DATE_ATOM),
        ];
    }
}
