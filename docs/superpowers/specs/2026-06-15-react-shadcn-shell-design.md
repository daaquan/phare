# React + shadcn/ui Shell, Drop DaisyUI — Design

**Date**: 2026-06-15
**Status**: Approved (design phase)
**Scope**: Sub-project #3 of "Match Laravel 12 React starter kit (Inertia + React + shadcn/ui)"

## Context

Sub-projects #1 (`Phare\Inertia` adapter) and #2 (Vite pipeline + `@vite`) are done on the
stacked branch `feat/vite-pipeline`. This sub-project stands up the **client** half — React +
`@inertiajs/react` + shadcn/ui — ports every currently-rendered page to React, and removes
DaisyUI. It is deliberately a single cohesive cut so there is **no broken intermediate state**:
DaisyUI is removed in the same change that ports the last DaisyUI-styled page.

Currently rendered pages (controllers returning `view()`):
- `IndexController::welcome` → `welcome` (marketing, 3 cards, auth-aware CTA)
- `IndexController::dashboard` → `dashboard` (stats + table, `auth` middleware)
- `Auth\LoginController::index` → `auth.login` (login form; `store`/`logout` are redirects)
- `PostController::index` → `posts/index` (paginated table)
- `errors/404.blade.php` (error page)

### Decisions locked during brainstorming
- **Scope**: infra + port all current pages + drop DaisyUI in one cut (no broken commit). Old "#4 port pages" folds in; remaining work becomes the new #4 = full L12 auth additions.
- **Language**: TypeScript (`.tsx`, `tsconfig.json`), matching the L12 React kit.
- **shadcn**: seed the L12 base component set the ported pages need.
- **Translations**: controllers resolve `__()` keys and pass them as page props. Japanese source stays in `lang/*.php` (honors the project's Japanese-strings rule). No JS i18n library.
- **Errors**: render error pages through an Inertia `Error.tsx` page (exception handler wiring required).
- **JS tests**: rely on `npm run build` (TypeScript typecheck) + Pest Inertia feature assertions. No vitest.

## Architecture

Units below are independently understandable; the implementation plan sequences them so the
app stays runnable after each major step (stack → theme → pages → wiring → DaisyUI removal last).

### A. Client stack (`/opt/phare`)

- **npm deps** (add): `react`, `react-dom`, `@inertiajs/react`, `@vitejs/plugin-react`,
  `typescript`, `@types/react`, `@types/react-dom`, `@types/node`; shadcn runtime:
  `tailwind-merge`, `clsx`, `class-variance-authority`, `lucide-react`, `tw-animate-css`, and
  the `@radix-ui/react-*` packages pulled in by the seeded components.
- **`vite.config.js`**: add `@vitejs/plugin-react`'s `react()`; change input to
  `resources/js/app.tsx`; add resolve alias `@` → `resources/js`. `refresh: true` stays.
- **`tsconfig.json`**: L12-style (`strict`, `jsx: react-jsx`, `baseUrl`, `paths: { "@/*": ["resources/js/*"] }`, bundler module resolution).
- **`resources/js/app.tsx`**: `createInertiaApp({ resolve: name => resolvePageComponent(name, import.meta.glob('./pages/**/*.tsx')), setup: ({ el, App, props }) => createRoot(el).render(<App {...props} />) })`. Progress bar via `@inertiajs/react`.

### B. Theme — remove DaisyUI

- **`resources/css/app.css`**: drop `@plugin "daisyui" { themes: winter; }`; keep
  `@import "tailwindcss"`; add `@import "tw-animate-css"`; add the shadcn design-token block
  (`:root` light + `.dark` CSS variables: `--background`, `--foreground`, `--primary`, `--border`,
  `--ring`, `--radius`, etc.) mapped through a Tailwind v4 `@theme inline` block; repoint
  `@source` to include `../js`; remove the `@tabler/icons-webfont` import (icons come from
  `lucide-react`).
- **Framework `Phare\View\ViewServiceProvider`**: remove the `$blade->useDaisyui()` call.
  (This is the only framework change in #3; lands on the same stacked branch.)
- **npm**: remove `daisyui` and `@tabler/icons-webfont`.

### C. shadcn/ui

- **`components.json`**: new-york style, `rsc: false`, Tailwind v4 (no config file), css
  `resources/css/app.css`, aliases `@/components`, `@/lib/utils`, `@/components/ui`.
- **`resources/js/lib/utils.ts`**: `cn()` (clsx + tailwind-merge).
- **`resources/js/components/ui/`** seeded set: `button`, `input`, `label`, `card`,
  `checkbox`, `dropdown-menu`, `avatar`, `separator`, `sheet`, `sidebar`. (Standard shadcn
  source, unmodified.)

### D. Layouts (React) + root view

- **`resources/views/app.blade.php`** — the sole server view, set as Inertia's root view:
  ```blade
  <!DOCTYPE html>
  <html lang="{{ config('app.locale') }}" @class(['dark' => false])>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name', 'Phare') }}</title>
    <link rel="icon" ...> {{-- existing favicons --}}
    @viteReactRefresh
    @vite(['resources/js/app.tsx'])
    @inertiaHead
  </head>
  <body>@inertia</body>
  </html>
  ```
- **`resources/js/layouts/AppLayout.tsx`**: authenticated shell — shadcn sidebar + header with
  user dropdown (avatar, logout). Used by Dashboard and Posts.
- **`resources/js/layouts/GuestLayout.tsx`**: centered card layout for Login.
- **Welcome** uses its own self-contained marketing layout (no app chrome).
- Delete the old Blade `layouts/*.blade.php` and `partials/*.blade.php` once pages are ported.

### E. Pages (`resources/js/pages/`)

Faithful ports of the existing markup, restyled with Tailwind + shadcn (no DaisyUI classes):
- **`Welcome.tsx`**: header logo + auth-aware CTA (Dashboard if `auth.user`, else Login), the
  three info cards (docs / phalcon / github), footer. Strings via props.
- **`Dashboard.tsx`** (AppLayout): three stat cards + a data table (shadcn `card`/`table`-style
  markup). Strings via props.
- **`auth/Login.tsx`** (GuestLayout): `useForm({ email, password, remember_me })` POST to
  `/login`; renders shared `errors` and `flash.error`. Strings via props.
- **`Posts/Index.tsx`** (AppLayout): paginated table from a `posts` prop (`{ data, links,
  meta }` shape produced by the controller); pagination links use Inertia `<Link>`.
- **`Error.tsx`**: generic error page taking a `status` prop (403/404/500/503) with localized
  title/description.

### F. Server wiring

- **`App\Http\Middleware\HandleInertiaRequests extends \Phare\Inertia\Middleware`**:
  - `version()` → Vite manifest hash (md5 of `public/build/manifest.json` when present, else null).
  - share `auth` → `['user' => Auth::user() ? {id,name,email} : null]`, `flash` →
    `['success' => ..., 'error' => ...]` from the flash session, `errors` → validation errors
    bag as `{ field: message }` (default `{}`).
  - Registered in the `web` middleware group in `App\Http\Kernel`.
- **Controllers** switch `view()` → `Inertia::render()`:
  - `IndexController`: `Inertia::render('Welcome', [...strings, ...])` and
    `Inertia::render('Dashboard', [...])`.
  - `PostController::index`: `Inertia::render('Posts/Index', ['posts' => $paginator->toArray(), 'title' => ...])`.
  - `LoginController::index`: `Inertia::render('auth/Login', [...strings])`. `store`/`logout`
    remain redirects — Inertia follows them (303 handled by the middleware).
- **`App\Exceptions\Handler`**: for rendered HTTP exceptions in non-debug mode, return
  `Inertia::render('Error', ['status' => $status])` with the matching status code (403, 404,
  419, 429, 500, 503). Debug mode keeps the existing verbose handler.
- **Inertia root view** is `app` (the factory default), satisfied by `app.blade.php`.

## Testing

### App — Pest feature tests (`tests/Feature/Inertia/`)
Send requests with the `X-Inertia` header and assert the JSON page object:
1. `GET /` → component `Welcome`; `auth.user` is null when guest.
2. `GET /` authenticated → `auth.user` populated; CTA-relevant prop present.
3. `GET /dashboard` as guest → redirect to login (auth middleware).
4. `GET /dashboard` authenticated → component `Dashboard`.
5. `GET /login` → component `auth/Login`.
6. `POST /login` bad credentials → redirect back with `flash.error` (shared errors visible on next Inertia load).
7. `GET /posts` → component `Posts/Index`; `posts.data` is an array; pagination meta present.
8. A forced HTTP 404 in non-debug → component `Error` with `status = 404`.

### Frontend
- `npm run build` completes (TypeScript typecheck + Vite build) and emits `app.tsx`'s chunk in the manifest.

### Manual verification
- `npm run dev` + load `/`, `/login`, `/dashboard` (after login), `/posts`: pages render with
  shadcn styling, no DaisyUI classes remain, HMR works.

## Out of scope (new #4)

- Full L12 auth additions: register, forgot/reset password, email verification, and settings
  (profile, password, appearance/dark-mode toggle), plus their backend routes/controllers.
- Dark-mode toggle UI (token block ships in #3; the switcher is #4).
- SSR.
