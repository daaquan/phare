# Full L12 Auth Parity — Design

**Date**: 2026-06-15
**Status**: Approved (design phase)
**Scope**: Sub-project #4 (final) of "Match Laravel 12 React starter kit (Inertia + React + shadcn/ui)"

## Context

Sub-projects #1–#3 delivered the Inertia adapter, the Vite pipeline, and the React + shadcn/ui
shell with Login, Dashboard, Posts, and the Error page (branch `feat/react-shadcn`). This
sub-project completes the Laravel 12 auth surface: **register, forgot/reset password, email
verification, and settings (profile / password / appearance)**, plus the supporting backend
infra.

The framework already ships the hard parts: `Phare\Auth\Passwords\PasswordBroker` +
`DatabaseTokenRepository`, `Phare\Mail\Mailer` (with a record-only `log` driver), and
`Phare\Notifications`. The app only needs configuration, a migration, DI wiring, controllers,
pages, and two middleware.

### Decisions locked during brainstorming
- **Scope**: full auth in one cut (register + reset + verify + settings), mail infra included.
- **Mail driver**: `log` (records to `storage/logs`, no SMTP; SMTP configurable via env later).
- **Appearance / dark mode**: included — light/dark/system toggle, `localStorage` + `.dark` class.
- **Settings routes**: `/settings/*` (L12 parity), explicit `#[Route]` paths on
  `App\Http\Controllers\Settings\*`.
- **Email verification**: **hard gate** — a `verified` middleware redirects unverified users to
  the verify page from protected routes.

## Backend infrastructure

- **`config/mail.php`**: `default => env('MAIL_MAILER','log')`; `mailers.log` (driver `log`),
  `mailers.smtp` (host/port/user/pass from env, `live => false` by default so it records);
  `from` address/name from env. Register `Phare\Mail\MailServiceProvider` in `config/app.php`
  providers (if not already present).
- **`password_reset_tokens` migration**: `database/migrations/mysql/*_create_password_reset_tokens_table.sql`
  (`email` PK, `token`, `created_at`) and the matching table in the Pest test schema builder so
  broker unit tests can run on sqlite.
- **`App\Providers\AuthServiceProvider`** (new, added to `config/app.php` providers): binds
  `Phare\Auth\Passwords\PasswordBroker` constructed from `DatabaseTokenRepository(PDO)` + the
  app hasher, with a 60-minute expiry. Exposes it as `password.broker`.
- **`User` model**: implement `Phare\Contracts\Auth\CanResetPassword`
  (`getEmailForPasswordReset()`); add `hasVerifiedEmail(): bool`,
  `markEmailAsVerified(): void` (sets `email_verified_at = now`, saves),
  `getKeyForVerification()`. Use the framework `Notifiable` trait for mail sending.
- **Middleware** (new, both `BeforeMiddleware`, registered as route middleware):
  - `App\Http\Middleware\RedirectIfAuthenticated` (`guest`): authed users → `/dashboard`.
  - `App\Http\Middleware\EnsureEmailIsVerified` (`verified`): unverified authed users →
    `/auth/verify-email`.

## Routes, controllers, pages

All auth controllers live under `App\Http\Controllers\Auth\` (`/auth` prefix); settings under
`App\Http\Controllers\Settings\` with explicit `/settings/*` paths. Guest-only pages use the
`guest` middleware; settings + verify use `auth` (and protected app routes add `verified`).

### Registration
- `RegisterController`: `GET /auth/register` (`guest`) → `Inertia::render('auth/Register')`;
  `POST /auth/register` (`guest`, `throttle`) → validate (name, email unique, password
  confirmed) via a `Web\RegisterRequest`, create the user, log in, send the verification mail,
  redirect `/dashboard`.
- Page `resources/js/pages/auth/Register.tsx` (GuestLayout): name / email / password /
  password_confirmation; `useForm` POST; renders shared `errors`.

### Forgot / reset password
- `PasswordResetLinkController`: `GET /auth/forgot-password` (`guest`) → `auth/ForgotPassword`;
  `POST /auth/forgot-password` (`guest`, `throttle`) → look up user, `broker->createToken`,
  mail the reset link `/auth/reset-password/{token}?email=...`, flash a neutral status (no user
  enumeration), redirect back.
- `NewPasswordController`: `GET /auth/reset-password/{token}` (`guest`) →
  `auth/ResetPassword` with `token` + `email` (from query); `POST /auth/reset-password`
  (`guest`) → validate (token, email, password confirmed), `broker->validateToken`, update the
  password, `broker->deleteToken`, log in, redirect `/dashboard`.
- Pages `auth/ForgotPassword.tsx`, `auth/ResetPassword.tsx`.

### Email verification (hard gate)
- `EmailVerificationPromptController`: `GET /auth/verify-email` (`auth`) → if already verified
  redirect `/dashboard`, else `Inertia::render('auth/VerifyEmail')` (with `status` flash on
  resend).
- `VerifyEmailController`: `GET /auth/verify-email/{id}/{hash}` (`auth`) → load user by `id`,
  check `hash_equals(sha1($user->email), $hash)`, mark verified, redirect `/dashboard?verified=1`.
  (Laravel's hash scheme; full URL signing is out of scope — the route is `auth`-guarded.)
- `EmailVerificationNotificationController`: `POST /auth/email/verification-notification`
  (`auth`, `throttle`) → resend the verification mail, flash status.
- Page `auth/VerifyEmail.tsx` (Guest/SimpleLayout): explanation + "resend" button + logout.
- The `verified` middleware is applied to `/dashboard`, `/posts`, and all `/settings/*` routes.

### Settings
- **Routing note**: the `Settings\` controllers must run through the `web` middleware group
  (so `HandleInertiaRequests` shares `auth`/`flash`/`errors`). The plan confirms how the route
  loader assigns the group to a non-`Api`/`Auth` sub-namespace; if it does not by default, the
  settings routes opt in explicitly. Without this the settings pages would lose `auth.user`.
- `SettingsLayout.tsx` (React): left tab nav (Profile / Password / Appearance), reused by all
  three pages; nested in `AppLayout`.
- `Settings\ProfileController`: `GET /settings/profile` (`auth`,`verified`) →
  `settings/Profile` with the user; `PATCH /settings/profile` → validate (name, email unique
  except self), update; if email changed, clear `email_verified_at` and resend verification.
- `Settings\PasswordController`: `GET /settings/password` → `settings/Password`;
  `PUT /settings/password` → validate (current_password matches, new password confirmed),
  update.
- `Settings\AppearanceController`: `GET /settings/appearance` → `settings/Appearance`
  (no write endpoint; the toggle is client-side).
- Pages `settings/Profile.tsx`, `settings/Password.tsx`, `settings/Appearance.tsx`.

## Frontend support

- **`useAppearance` hook** (`resources/js/hooks/use-appearance.ts`): reads/writes
  `localStorage('appearance')` (`light`|`dark`|`system`), toggles the `.dark` class on
  `document.documentElement`, and listens to the system `prefers-color-scheme` media query.
- **Pre-paint theme script** in `app.blade.php` `<head>`: an inline `<script>` that applies the
  stored (or system) theme before first paint to avoid a flash. (This is the one inline script;
  it reads only `localStorage`, no external source.)
- **AppLayout**: add a Settings link to the sidebar and a dismissable "verify your email"
  banner shown when `auth.user` is unverified (a new shared `auth.user.email_verified` prop).
- Validation errors render from the shared `errors` prop; form requests populate it (see below).

## Validation errors → Inertia `errors` prop

`#3` left the shared `errors` prop as an empty object. This sub-project wires real field
errors: web `FormRequest` validation failures flash a `{field: message}` bag to the session,
and `HandleInertiaRequests` shares it as `errors` (replacing the empty default), then clears it.
This is required for register/reset/profile/password forms to show inline errors.

## Mail

- `App\Notifications\VerifyEmailNotification` and `App\Notifications\ResetPasswordNotification`
  (or simple `Mailable`s) build the link bodies and send via the framework `Mailer`. With the
  `log` driver the rendered message lands in `storage/logs`, which is sufficient for dev and
  for asserting "a mail was recorded" in tests.

## Testing

- **Inertia page renders** (Pest feature, `tests/Feature/Auth/`, `tests/Feature/Settings/`):
  `GET` each page with the `X-Inertia` header → assert the component
  (`auth/Register`, `auth/ForgotPassword`, `auth/ResetPassword`, `auth/VerifyEmail`,
  `settings/Profile`, `settings/Password`, `settings/Appearance`). Guest pages redirect when
  authenticated; settings/verify redirect when guest.
- **Unit tests**: email-verify `hash_equals(sha1(email), hash)` accept/reject; `PasswordBroker`
  token create → validate → expire (sqlite-backed token repo, no model relations, so no
  segfault); the `errors`-sharing path in `HandleInertiaRequests`.
- **Validation**: DB-free request-validation tests (missing fields → 422 / flashed errors).
- **DB-write flows** (register-create, reset-update, profile-update) that touch the `User`
  model under the pre-existing sqlite ORM segfault are skipped with the same documented note as
  #3; the controllers are exercised manually with the `log` mailer.
- **Frontend**: `npm run build` + `tsc --noEmit` pass.

## Out of scope

- Real SMTP delivery (env-configurable, not wired for dev), URL signature verification beyond
  the `sha1(email)` hash + `auth` guard, 2FA / passkeys (framework has a `Passkeys` namespace;
  not part of the L12 React kit baseline), and remember-me persistence beyond the existing
  login flow.
