<?php

namespace App\Events;

use Phare\Broadcasting\BroadcastEvent;
use Phare\Broadcasting\PresenceChannel;
use Phare\Broadcasting\PrivateChannel;

/**
 * 配信スタックの疎通確認用デモイベント。監視画面のテスト送信ボタンや
 * アプリ内のメッセージ通知から dispatch する。
 *
 * 使い方: broadcast(new MessageBroadcast($userId, 'hello'))->send();
 */
class MessageBroadcast extends BroadcastEvent
{
    public function __construct(
        public int $userId,
        public string $message,
    ) {}

    /**
     * private-App.User.{id}（本人宛）と presence-monitor（監視）に流す。
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
