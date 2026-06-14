# Inertia Server Adapter for Phare — Design

**Date**: 2026-06-14
**Status**: Approved (design phase)
**Scope**: Sub-project #1 of "Match Laravel 12 React starter kit (Inertia + React + shadcn/ui)"

## Context

Phare is a Phalcon-based PHP framework with Laravel-like conventions (`/opt/framework`,
linked into `/opt/phare` via a `vendor/phare/framework -> /opt/framework` symlink). The app
currently renders Blade views styled with DaisyUI.

Goal of the parent effort: fully match the Laravel 12 React starter kit — Inertia protocol +
React + shadcn/ui — and discard DaisyUI entirely. That effort decomposes into independent
sub-projects, each with its own spec → plan → implementation cycle:

1. **Inertia server adapter** (this spec) — protocol foundation, everything depends on it
2. Vite build pipeline (`@vite` Blade directive + manifest, `@vitejs/plugin-react`), replacing Laravel Mix
3. React + shadcn/ui app shell, Tailwind v4 theme tokens, drop DaisyUI
4. Port pages (welcome, dashboard) to React
5. Full L12 auth parity (login, register, forgot/reset, verify, settings: profile/password/appearance)

This spec covers **only #1**. It produces a server adapter that can be unit-tested standalone
against a stub root view — no Vite, React, or pages required yet.

### Decisions locked during brainstorming
- **Adapter home**: `/opt/framework` as `Phare\Inertia\*` (reusable package home, matches how Laravel ships Inertia support)
- **Auth scope (parent effort)**: full L12 parity (deferred to sub-project #5)
- **API surface**: Facade (`Inertia::render(...)`) + `inertia()` helper + alias in `config/app.php`
- **Tests**: Pest (framework already has `vendor/bin/pest`)

## Architecture

Mirrors `inertia-laravel`'s server contract. New namespace `Phare\Inertia` under
`/opt/framework/src/Phare/Inertia/`.

| Class | Role |
|-------|------|
| `ResponseFactory` (service `inertia`) | API: `render($component, $props = [])`, `share($key, $value)`, `getShared($key = null)`, `version($version)`, `lazy($callback)`, `optional($callback)`, `location($url)` |
| `Response` | Renderable. Holds `component`, `props`, `rootView`, `version`, merged shared props. `toResponse(Request): Phare\Http\Response` resolves props → builds page object → branches JSON vs HTML |
| `Middleware` (HandleInertiaRequests) | Sets asset `version` + shared props (`auth.user`, `flash`, validation `errors`); converts version mismatch to 409; forces 303 on PUT/PATCH/DELETE redirects |
| `Directive` registration | `@inertia` → root `<div id="app" data-page='{json}'></div>`; `@inertiaHead` → no-op placeholder for now (SSR deferred) |
| `LazyProp` / `OptionalProp` | Marker wrappers for closures evaluated only on partial reloads |
| `InertiaServiceProvider` | Binds `inertia` singleton, registers Blade directives, registers facade alias |
| `Phare\Support\Facades\Inertia` | Facade resolving to `inertia` service |
| `inertia()` helper | Returns `inertia` service; `inertia($component, $props)` shorthand for `render` |

### Root view (lives in app repo, #2/#3)
`resources/views/app.blade.php` contains `@vite(...)` (added in #2) and `@inertia`. For #1's
tests, a minimal stub root view is used so HTML branch is exercisable without Vite.

## Data flow — `Response::toResponse(Request)`

1. **Resolve props**
   - Merge shared props (deep, dot-notation aware) with per-response props.
   - **Partial reload**: if request has `X-Inertia-Partial-Data` AND `X-Inertia-Partial-Component` equals this component → keep only allowlisted top-level prop keys; otherwise drop `LazyProp`/`OptionalProp` entries.
   - Evaluate remaining closures / `LazyProp` / `OptionalProp` to values. Non-lazy closures always evaluate.
2. **Build page object**: `{ component, props, url: request full path+query, version }`.
3. **Branch**
   - **`X-Inertia` header present** → `Response->json(page)` with headers `X-Inertia: true`, `Vary: X-Inertia`, status 200.
   - **No `X-Inertia`** → render `rootView` Blade, passing `page` (the `@inertia` directive emits the `data-page` div). Standard HTML 200.

## Protocol edge cases (must be covered)

- **Version mismatch**: request is GET + has `X-Inertia` + `X-Inertia-Version` ≠ current version → respond `409 Conflict` with `X-Inertia-Location: <current url>`. Client performs hard reload. (Handled in `Middleware`.)
- **Redirect method coercion**: response is a redirect (300–308) AND request method ∈ {PUT, PATCH, DELETE} → force status `303 See Other`. (Handled in `Middleware`.)
- **Shared props**: deep-merged into every Inertia response. Defaults: `auth.user` (current user or null), `flash` (session flash bag), `errors` (validation errors from session, as `{field: message}`).
- **Lazy/optional props**: `Inertia::lazy(fn)` / `Inertia::optional(fn)` only evaluated when the prop is explicitly requested in a partial reload; excluded otherwise.
- **Empty `errors`**: always present as `{}` so the client `errors` prop is stable.

## Dependencies (all already present in framework)

- `Phare\Http\Request` — header access (`X-Inertia*`), method, full URL
- `Phare\Http\Response::json()` / `withHeaders()` — JSON branch + headers
- `Phare\View\Factory` — render root view (HTML branch)
- Session / flash — shared `flash` and `errors`
- `Phare\Support\ServiceProvider`, `Phare\Support\Facades\Facade` — provider + facade base

## DaisyUI removal touch-point (noted, executed in #3 not here)

`Phare\View\ViewServiceProvider` calls `$blade->useDaisyui()` and the layout uses `@cssbox` +
`data-theme`. Sub-project #3 removes `useDaisyui()`, drops `@cssbox`/`data-theme`, and removes
the `daisyui` npm dependency. **Out of scope for #1.**

## Testing (Pest, in `/opt/framework/tests/Inertia/`)

TDD, one behavior per test:

1. `render()` returns a `Response` with component + props.
2. Request **with** `X-Inertia` → JSON body equals page object; has `X-Inertia: true` and `Vary: X-Inertia` headers.
3. Request **without** `X-Inertia` → HTML containing `id="app"` and a `data-page` attribute holding the page JSON (asserted against stub root view).
4. **Partial reload**: matching `X-Inertia-Partial-Component` + `X-Inertia-Partial-Data: a,b` → only props `a`, `b` present.
5. Partial reload with **non-matching** component → full prop set returned.
6. **Lazy prop** excluded on normal load; included when named in partial data.
7. **Version mismatch** (GET + X-Inertia + stale version) → 409 + `X-Inertia-Location`.
8. **Redirect coercion**: PUT redirect → 303.
9. **Shared props**: `share('auth', ...)` appears merged in every response; deep-merge does not clobber sibling keys.
10. `errors` defaults to `{}` when none flashed.

Target ≥80% coverage on the new namespace.

## Out of scope (separate specs)

- `@vite` directive, Vite config, manifest reading (#2)
- React, `@inertiajs/react`, shadcn/ui, Tailwind theme, DaisyUI removal (#3)
- Page components (#4)
- Auth scaffolding (#5)
- SSR (`@inertiaHead` is a placeholder no-op for now)
