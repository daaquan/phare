# Broadcasting (Laravel Echo equivalent) + monitor admin screen — design

Date: 2026-06-26
Status: in progress

## Goal

Wire real-time broadcasting through the app (`/opt/phare`) at a level equivalent to Laravel
Echo, and provide an admin screen (the broadcasting monitor) for observing it.

The server-side foundation (`Phare\Broadcasting\*`: BroadcastManager, the Pusher / Redis /
Log / Null broadcasters, channel authorisation) already exists in the framework. This work
covers the app-side wiring, the JS client, and the monitor dashboard.

## Decisions

- **Transport**: Soketi (self-hosted, Pusher protocol compatible). The framework's
  `PusherBroadcaster` points straight at it. Stands in for Reverb.
- **JS client**: the real `laravel-echo` + `pusher-js` npm packages -- no home-grown Echo.
- **Admin scope**: the broadcasting monitor only, not a general-purpose admin.
- **Admin access control**: a `users.is_admin` boolean plus an `admin` route middleware.

## Data flow

```
PHP event -> Broadcast facade -> PusherBroadcaster -> Soketi(WS) -> laravel-echo (browser)
                                     ^                      |
                        POST /broadcasting/auth   private/presence subscription
Monitor: Admin\BroadcastingController -> Pusher SDK (getPusher()->getChannels etc.)
         -> Soketi HTTP API -> React dashboard (polling)
```

## Components

### Server wiring (app)
- `config/broadcasting.php` — default to `pusher`, with Soketi's host/port/scheme from env.
  Locally `useTLS=false`.
- `routes/channels.php` — channel authorisation callbacks (`Broadcast::channel(...)`),
  demonstrating a private `App.User.{id}` and a `presence-monitor` presence channel.
- Add `App\Providers\BroadcastServiceProvider` (framework) to the providers to enable the
  `broadcast` service and the `Broadcast` alias.
- `App\Http\Controllers\Broadcasting\AuthController` — `POST /broadcasting/auth`
  (middleware `auth`). Loads `routes/channels.php` and returns `Broadcast::auth($request)`.
  Channels are registered lazily per request to stay independent of boot order.
- `App\Events\MessageBroadcast` — demo event sent to a private and a presence channel.

### Frontend Echo client
- `npm i laravel-echo pusher-js`
- `resources/js/echo.ts` — Echo initialisation (broadcaster `pusher`, wsHost/wsPort from Vite
  env, authEndpoint `/broadcasting/auth`, X-CSRF-TOKEN). Exposed on `window.Echo`.
- `resources/js/hooks/use-echo.ts` — the thin `useEcho(channel, event, cb)` hook.
- `app.tsx` imports `echo.ts`.

### Monitor admin screen (`/admin/broadcasting`)
- `App\Http\Controllers\Admin\BroadcastingController`
  - `GET /admin/broadcasting` -> `Inertia::render('admin/Broadcasting')`
  - `GET /admin/broadcasting/channels` -> Soketi's occupied channels (JSON)
  - `GET /admin/broadcasting/channels/{name}` -> occupancy/subscription count + presence members (JSON)
  - `POST /admin/broadcasting/test` -> dispatch `MessageBroadcast`
  - all behind middleware `['auth','verified','admin']`
  - when Soketi is down, try/catch returns empty results plus a warning log rather than failing
- `resources/js/pages/admin/Broadcasting.tsx` — channel table (name/type/subscribers),
  presence members, automatic polling, and a test-send button proving live receipt.

### Access control
- Migration: `users.is_admin` boolean default false.
- `App\Http\Middleware\EnsureUserIsAdmin` plus Kernel `routeMiddleware['admin']`.

## Out of scope (YAGNI)
- A persistent event log: Soketi keeps no history. Add it later by receiving Soketi webhooks.
- A general app admin (user/post management and so on): scoped to monitoring only.

## Testing
- Unit tests for channel authorisation and for events' broadcastOn/broadcastWith.
- Rendering tests for the monitor controller (per CLAUDE.md, assert the Inertia component
  rather than redirects; DB-write flows are skipped due to the sqlite driver segfault).
- Tests for the channels endpoint with the Pusher SDK mocked.

## Running locally
- `npx @soketi/soketi start` (or docker). Env: `PUSHER_*` (app id/key/secret) plus
  `PUSHER_HOST=127.0.0.1`, `PUSHER_PORT=6001`, `PUSHER_SCHEME=http`.
