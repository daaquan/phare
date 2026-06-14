# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Phare is a PHP game framework built on top of Phalcon (v5.4+) with Laravel-inspired conventions. It uses `phare/framework` (a separate repo at github.com/daaquan/framework) as its core dependency. The framework provides Eloquent-like ORM, Blade templating, service containers, facades, and artisan-like CLI commands.

## Common Commands

```bash
# Install dependencies
composer install
npm install

# Run dev server
php artisan serve

# Run tests (Pest PHP, uses SQLite in-memory for testing)
./vendor/bin/pest
./vendor/bin/pest tests/Feature/SomeTest.php        # single file
./vendor/bin/pest --filter="test name"               # single test

# Code style (Laravel Pint, PSR-12 based)
./vendor/bin/pint
./vendor/bin/pint --test                             # check without fixing

# Frontend assets (Vite + React + Inertia + shadcn/ui + Tailwind v4)
npm run dev        # Vite dev server (HMR; writes public/hot)
npm run build      # production build (writes public/build + manifest.json)

# Artisan commands
php artisan migrate
php artisan make:migration <name>
php artisan key:generate
php artisan queue:work
```

## Architecture

- **Framework core**: `phare/framework` package — all `Phare\` namespaced classes live there, not in this repo
- **Bootstrap**: `bootstrap/app.php` creates `Phare\Foundation\Web` instance, binds HTTP kernel, console kernel, and exception handler
- **Service Providers**: Registered in `config/app.php` `providers` array. Framework providers (`Phare\Providers\*`) load first, then app providers (`App\Providers\*`)
- **Facades/Aliases**: Defined in `config/app.php` `aliases` — maps short names like `DB`, `Auth`, `Log` to `Phare\Support\Facades\*`
- **Routing**: Attribute-based (`#[Route]`, `#[RoutePrefix]`) on controllers by default (controllers scanned under `app/Http/Controllers`). HTTP method + sub-namespace determine the URI/middleware group (`Api\` → `/api`, `Auth\` → `/auth`). Alternatively, set `app.route_loader = 'file'` to load file-based routes from `routes/` (e.g. `routes/callbacks.php`) instead — the two loaders are mutually exclusive.
- **Repository Pattern**: `App\Contracts\Repository\*` interfaces with `App\Repository\*` implementations
- **ORM**: Phalcon-based but with Eloquent-like API (`Phare\Eloquent\Model`). Models define `$connection`, `$table`, `$fillable`, `$hidden`, `$casts`
- **Controllers split**: `Http/Controllers/Api/` (JSON API) and `Http/Controllers/Auth/`, `Http/Controllers/Settings/` + `Http/Controllers/` (web — render Inertia pages via `Inertia::render('Component', $props)`)
- **Request validation**: Separate request classes in `Http/Requests/Api/` and `Http/Requests/Web/`

## Frontend (Inertia + React + shadcn/ui)

- **Stack**: Vite (`laravel-vite-plugin` + `@vitejs/plugin-react` + `@tailwindcss/vite`), React 19, `@inertiajs/react`, TypeScript, shadcn/ui (new-york), Tailwind v4 (CSS-first, no `tailwind.config.js`). **No Laravel Mix, no DaisyUI, no Blade page views.**
- **Server adapter**: `Phare\Inertia` (framework). Controllers return `Inertia::render(...)`; a `dispatch:afterExecuteRoute` bridge renders it to the response. `@vite` / `@viteReactRefresh` / `@inertia` Blade directives live in the framework.
- **Root view**: `resources/views/app.blade.php` is the ONLY Blade file — `@vite(['resources/css/app.css','resources/js/app.tsx'])` + `@inertia`. Everything else is React.
- **JS layout**: `resources/js/` — `app.tsx` (createInertiaApp, resolves `pages/**/*.tsx`), `pages/`, `layouts/` (AppLayout sidebar, GuestLayout, SettingsLayout), `components/ui/` (shadcn), `lib/utils.ts` (`cn`), `hooks/`, `types/`. Path alias `@/*` → `resources/js/*`.
- **Shared props**: `App\Http\Middleware\HandleInertiaRequests` shares `auth.user`, `flash`, and validation `errors` (controllers flash field errors to the session via `Controller::backWithErrors`). Theme tokens (`.dark`) ship in `app.css`; the appearance toggle is client-side (`useAppearance` + a pre-paint script in `app.blade.php`).
- **Auth**: full set — login, register, forgot/reset password (`PasswordBroker` + `log` mailer), email verification (hard `verified` gate, `sha1(email)` hash), settings (`/settings/{profile,password,appearance}`). `guest`/`verified` route middleware in `App\Http\Kernel`.
- **Build gotcha**: rebuild (`npm run dev` / `npm run build`) after editing pages/components; Tailwind v4 JIT only emits classes seen in `@source` (`resources/js`, `resources/views`) at build time.
- **Caveat**: route middleware (`auth`/`guest`/`verified`) run via Phalcon `application:beforeHandleRequest` events in production but are a no-op in the Pest test harness, so feature tests assert the rendered Inertia component, not redirects. DB-write flows segfault under the sqlite test driver (pre-existing Phalcon ORM issue) and are skipped.

## Key Configuration

- **PHP 8.2+** required, with Phalcon extension (`ext-phalcon ~5.4`)
- **Pint config**: `pint.json` — Laravel preset with custom rules (concat spacing, cast spacing, etc.)
- **Test env**: `phpunit.xml` sets `APP_ENV=testing`, `DB_CONNECTION=sqlite`, `DB_DATABASE=testing`, `CACHE_DRIVER=array`, `QUEUE_CONNECTION=sync`
- **Phalcon ORM settings**: Configured in `config/app.php` under `phalcon.orm` — notably `exception_on_failed_save=true`, `ignore_unknown_columns=true`

## Language

This project's code comments, config comments, and README are in Japanese. Maintain Japanese for user-facing strings and documentation.
