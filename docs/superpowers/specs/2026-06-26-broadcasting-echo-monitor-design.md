# Broadcasting (Laravel Echo 同等) + 監視管理画面 — 設計

日付: 2026-06-26
ステータス: 実装中

## 目的

リアルタイム配信機能を Laravel Echo 同等のレベルでアプリ (`/opt/phare`) に通す。
あわせて配信状況を見る管理画面 (broadcasting monitor) を提供する。

サーバ側の配信基盤 (`Phare\Broadcasting\*`: BroadcastManager / Pusher・Redis・Log・Null
broadcaster / channel 認可ロジック) は framework 側に既存。本作業はアプリ側の配線と
JS クライアント、監視ダッシュボードの追加。

## 決定事項

- **転送**: Soketi (self-hosted, Pusher プロトコル互換)。framework の `PusherBroadcaster`
  をそのまま Soketi に向ける。Reverb 代替。
- **JS クライアント**: 本物の `laravel-echo` + `pusher-js` npm を利用 (Echo を自作しない)。
- **管理画面スコープ**: broadcasting monitor のみ (汎用 admin ではない)。
- **管理画面アクセス制御**: `users.is_admin` boolean + `admin` route-middleware。

## データフロー

```
PHP event → Broadcast facade → PusherBroadcaster → Soketi(WS) → laravel-echo(browser)
                                     ↑                    ↓
                        POST /broadcasting/auth   private/presence 購読
監視: Admin\BroadcastingController → Pusher SDK (getPusher()->getChannels 等)
       → Soketi HTTP API → React ダッシュボード(poll)
```

## コンポーネント

### サーバ配線 (アプリ)
- `config/broadcasting.php` — `pusher` を既定にし Soketi の host/port/scheme を env から。
  ローカルは `useTLS=false`。
- `routes/channels.php` — チャンネル認可コールバック (`Broadcast::channel(...)`)。
  `App.User.{id}` private と `presence-monitor` presence のデモ。
- `App\Providers\BroadcastServiceProvider`（framework）を providers に追加し `broadcast`
  サービスと `Broadcast` alias を有効化。
- `App\Http\Controllers\Broadcasting\AuthController` — `POST /broadcasting/auth`
  (middleware `auth`)。`routes/channels.php` を読み込み `Broadcast::auth($request)` を返す。
  チャンネル登録はリクエスト時に lazy に行いブート順依存を避ける。
- `App\Events\MessageBroadcast` — private + presence に流すデモイベント。

### フロント Echo クライアント
- `npm i laravel-echo pusher-js`
- `resources/js/echo.ts` — Echo 初期化 (broadcaster `pusher`、wsHost/wsPort を Vite env、
  authEndpoint `/broadcasting/auth`、X-CSRF-TOKEN)。`window.Echo` に公開。
- `resources/js/hooks/use-echo.ts` — `useEcho(channel, event, cb)` 薄いフック。
- `app.tsx` で `echo.ts` を import。

### 監視管理画面 (`/admin/broadcasting`)
- `App\Http\Controllers\Admin\BroadcastingController`
  - `GET /admin/broadcasting` → `Inertia::render('admin/Broadcasting')`
  - `GET /admin/broadcasting/channels` → Soketi の占有チャンネル一覧 (JSON)
  - `GET /admin/broadcasting/channels/{name}` → 占有/購読数 + presence メンバー (JSON)
  - `POST /admin/broadcasting/test` → `MessageBroadcast` を dispatch
  - すべて middleware `['auth','verified','admin']`
  - Soketi 未起動時は try/catch で空 + 警告ログ (落とさない)
- `resources/js/pages/admin/Broadcasting.tsx` — チャンネル表 (名前/種別/購読数)、
  presence メンバー、自動 poll、テスト送信ボタンでライブ受信を確認。

### アクセス制御
- マイグレーション: `users.is_admin` boolean default false。
- `App\Http\Middleware\EnsureUserIsAdmin` + Kernel `routeMiddleware['admin']`。

## スコープ外 (YAGNI)
- 永続イベントログ: Soketi は履歴を持たない。必要時に Soketi webhook 受信で追加。
- 汎用 app admin (ユーザー/投稿管理など): 監視のみにスコープ。

## テスト
- チャンネル認可 / イベントの broadcastOn・broadcastWith のユニットテスト。
- 監視コントローラの描画テスト (CLAUDE.md の通り、リダイレクトでなく Inertia
  コンポーネントを assert。DB 書き込み系は sqlite ドライバの segfault で skip)。
- Pusher SDK をモックして channels エンドポイントのテスト。

## ローカル実行
- `npx @soketi/soketi start`（または docker）。env は `PUSHER_*`（app id/key/secret）+
  `PUSHER_HOST=127.0.0.1` `PUSHER_PORT=6001` `PUSHER_SCHEME=http`。
