# 1:1 / ルームチャット — 設計

日付: 2026-07-06
ステータス: 設計承認済み・未実装（次セッションで着手）

## 目的

既存の broadcasting 基盤（`docs/superpowers/specs/2026-06-26-broadcasting-echo-monitor-design.md`、
Soketi + laravel-echo + `Phare\Broadcasting\*`）の上に、1:1 DM と複数人ルームの両方に
対応するチャット機能を追加する。

## 決定事項

- **スコープ**: 1:1 DM と複数人ルームを最初から両方サポート（参加者数で暗黙的に区別）。
- **UI**: 専用ページ `/chat`（サイドバー常駐ウィジェットではない）。
- **既読管理**: 未読件数バッジに加え、既読マーク（LINE風）も実装。
- **ルーム管理**: 会話作成時のメンバー選択のみ。作成後のメンバー追加/退出/ルーム名変更は無し（YAGNI）。

## アーキテクチャ

```
Send: React → POST /chat/{conv}/messages → Message保存 → broadcast(MessageSent)
      → PrivateChannel("Conversation.{id}") → 他参加者のEcho
Read: React(既読) → POST /chat/{conv}/read → last_read_at更新 → broadcast(ConversationRead)
      → 同チャンネル → 送信者側で既読マーク更新
```

既存基盤（PusherBroadcaster / Soketi / `routes/channels.php` / `useEcho`）をそのまま流用。
チャンネルは会話ごとに `private-Conversation.{id}`。

## データモデル

- `conversations` (id, type: dm|room, name nullable, created_at)
- `conversation_participants` (conversation_id, user_id, last_read_at, PK複合)
- `messages` (id, conversation_id, user_id, body, created_at)

## チャンネル認可（`routes/channels.php` に追記）

```php
Broadcast::channel('Conversation.{id}', function (?User $user, $id) {
    return $user !== null && ConversationParticipant::where('conversation_id', $id)
        ->where('user_id', $user->id)->exists();
});
```

## API / ルート

- `GET /chat` — 会話一覧 + 新規作成モーダル用ユーザー一覧（Inertia）
- `GET /chat/{conversation}` — 該当会話のメッセージ履歴込み（Inertia）
- `POST /chat` — 会話作成（`participant_ids[]`）→ 1人ならdm、2人以上ならroom
- `POST /chat/{conversation}/messages` — 送信 → 保存 → `MessageSent` broadcast
- `POST /chat/{conversation}/read` — `last_read_at` 更新 → `ConversationRead` broadcast

## フロント

- `resources/js/pages/Chat.tsx` — 左: 会話一覧（未読件数バッジ）、右: スレッド
- `useEcho` で `Conversation.{id}` 購読（`message` / `read` イベント）
- 自分のメッセージへの既読マーク: 相手の `last_read_at` >= メッセージ時刻で判定

## スコープ外（YAGNI）

- メンバー追加/退出/ルーム名変更
- オンライン状態表示（presence channel は使わない）
- 添付ファイル、タイピングインジケーター

## テスト方針

- チャンネル認可（参加者/非参加者）ユニットテスト
- `MessageSent` / `ConversationRead` の broadcastOn/broadcastWith ユニットテスト
- DB書き込み系フロー（会話作成/送信）は CLAUDE.md記載の既知の sqlite segfault で skip、
  Inertia描画のみ assert

## 次のステップ

次セッションで `writing-plans` スキルを使い実装計画に落とす。
