# 1:1 / room chat — design

Date: 2026-07-06
Status: design approved, not implemented (work starts next session)

## Goal

Add chat covering both 1:1 DMs and multi-person rooms on top of the existing
broadcasting foundation (`docs/superpowers/specs/2026-06-26-broadcasting-echo-monitor-design.md`,
Soketi + laravel-echo + `Phare\Broadcasting\*`).

## Decisions

- **Scope**: support 1:1 DMs and multi-person rooms from the start (distinguished implicitly by participant count).
- **UI**: a dedicated `/chat` page, not a sidebar widget.
- **Read state**: unread count badges plus read receipts (LINE style).
- **Room management**: member selection at creation time only. No adding members, leaving, or renaming afterwards (YAGNI).

## Architecture

```
Send: React -> POST /chat/{conv}/messages -> save Message -> broadcast(MessageSent)
      -> PrivateChannel("Conversation.{id}") -> the other participants' Echo
Read: React (read) -> POST /chat/{conv}/read -> update last_read_at -> broadcast(ConversationRead)
      -> same channel -> sender's read receipt updates
```

The existing foundation (PusherBroadcaster / Soketi / `routes/channels.php` / `useEcho`) is
reused as is. Each conversation gets the channel `private-Conversation.{id}`.

## Data model

- `conversations` (id, type: dm|room, name nullable, created_at)
- `conversation_participants` (conversation_id, user_id, last_read_at, composite PK)
- `messages` (id, conversation_id, user_id, body, created_at)

## Channel authorisation (appended to `routes/channels.php`)

```php
Broadcast::channel('Conversation.{id}', function (?User $user, $id) {
    return $user !== null && ConversationParticipant::where('conversation_id', $id)
        ->where('user_id', $user->id)->exists();
});
```

## API / routes

- `GET /chat` — conversation list plus the user list for the create modal (Inertia)
- `GET /chat/{conversation}` — that conversation including its message history (Inertia)
- `POST /chat` — create a conversation (`participant_ids[]`): one participant means dm, two or more means room
- `POST /chat/{conversation}/messages` — send: save, then broadcast `MessageSent`
- `POST /chat/{conversation}/read` — update `last_read_at`, then broadcast `ConversationRead`

## Frontend

- `resources/js/pages/Chat.tsx` — left: conversation list (with unread badges), right: the thread
- `useEcho` subscribes to `Conversation.{id}` (`message` / `read` events)
- Read receipts on your own messages: shown when the other party's `last_read_at` >= the message timestamp

## Out of scope (YAGNI)

- Adding members, leaving, renaming rooms
- Online status (no presence channel)
- Attachments, typing indicators

## Testing approach

- Unit tests for channel authorisation (participant / non-participant)
- Unit tests for broadcastOn/broadcastWith on `MessageSent` / `ConversationRead`
- DB-write flows (creating a conversation, sending) are skipped because of the known sqlite
  segfault documented in CLAUDE.md; only the Inertia rendering is asserted

## Next step

Turn this into an implementation plan next session using the `writing-plans` skill.
