# Vite Pipeline + `@vite` Directive — Design

**Date**: 2026-06-14
**Status**: Approved (design phase)
**Scope**: Sub-project #2 of "Match Laravel 12 React starter kit (Inertia + React + shadcn/ui)"

## Context

Sub-project #1 added the `Phare\Inertia` server adapter (`feat/inertia-adapter`). The Inertia
HTML response renders a root Blade view that must load a JS bundle. The Laravel 12 starter kit
uses **Vite** (not Laravel Mix) with `laravel-vite-plugin`. This sub-project replaces the
current Laravel Mix build with Vite and adds a framework `@vite` Blade directive.

The app currently builds Tailwind v4 + DaisyUI through Laravel Mix (`webpack.mix.js`,
`@tailwindcss/postcss`) and loads assets in `layouts/default.blade.php` /
`layouts/auth.blade.php` via `asset('css/app.css')` + `asset('js/app.js')` + the `@cssbox`
BladeOne directive.

### Decisions locked during brainstorming
- **`@vite` directive home**: `/opt/framework` (`Phare\View\Vite`), consistent with the Inertia adapter
- **Laravel Mix**: dropped fully; Vite only
- **Vite config**: use `laravel-vite-plugin` (gives hot file, manifest, dev-server parity)
- **Entry paths**: move to L12 convention — `resources/css/app.css`, `resources/js/app.js`
- **Provider**: fold `@vite` / `@viteReactRefresh` directive registration into `ViewServiceProvider`

## Architecture

### Server side — `/opt/framework/src/Phare/View/Vite.php`

Invokable `Vite` class mirroring `laravel-vite-plugin`'s server `Vite`:

- **Paths** (relative to `public_path()`):
  - hot file: `public/hot`
  - build directory: `public/build`
  - manifest: `public/build/manifest.json`, falling back to `public/build/.vite/manifest.json` (Vite 5+ default)
- **`__invoke(string|array $entrypoints): string`**:
  - **Dev mode** (hot file exists): read dev server URL from the hot file. Emit
    `<script type="module" src="{devUrl}/@vite/client"></script>` followed by a module
    `<script>` tag per entrypoint pointing at `{devUrl}/{entry}`.
  - **Build mode** (no hot file): load the manifest. For each entrypoint resolve its
    chunk; emit `<link rel="stylesheet">` for `.css` entries, `<script type="module">`
    for JS entries, and additionally emit `<link>` tags for any CSS listed under the JS
    chunk's `css` array. Asset URLs are `/build/{file}`.
  - **Missing manifest** in build mode → throw a clear `\RuntimeException` naming the
    expected manifest path.
- **`reactRefresh(): string`**: in dev mode emit the React Fast Refresh preamble
  (`<script type="module">` injecting `$RefreshReg$`/`$RefreshSig$` + import of
  `{devUrl}/@react-refresh`); empty string in build mode. Backs `@viteReactRefresh`,
  which sub-project #3 consumes.

### Blade directives (registered in `ViewServiceProvider`)

- `@vite($entrypoints)` → compiles to `echo (new \Phare\View\Vite())($entrypoints);`
- `@viteReactRefresh` → compiles to `echo (new \Phare\View\Vite())->reactRefresh();`

The `Vite` instance resolves `public_path()` itself, so the directives need no container
wiring. Registration sits alongside the existing Blade setup in `ViewServiceProvider`.

### Build side — `/opt/phare`

- **Remove**: `laravel-mix`, `webpack`, `webpack-cli`, `@babel/*`, `babel-loader`,
  `sass`, `sass-loader`, `resolve-url-loader`, `@tailwindcss/postcss`, `autoprefixer`,
  `postcss`; delete `webpack.mix.js` and `postcss.config.js`.
- **Add** (devDependencies): `vite`, `laravel-vite-plugin`, `@tailwindcss/vite`.
  Keep `daisyui` and `@tabler/icons-webfont` for now (removed in #3).
- **`package.json` scripts**: `"dev": "vite"`, `"build": "vite build"` (drop the Mix script set).
- **`vite.config.js`**:
  ```js
  import { defineConfig } from 'vite';
  import laravel from 'laravel-vite-plugin';
  import tailwindcss from '@tailwindcss/vite';

  export default defineConfig({
      plugins: [
          laravel({
              input: ['resources/css/app.css', 'resources/js/app.js'],
              refresh: true,
          }),
          tailwindcss(),
      ],
  });
  ```
  (React plugin + `.tsx` entry added in #3.)
- **Move entry files**:
  - `resources/assets/css/app.css` → `resources/css/app.css` (unchanged content; still
    imports Tailwind + DaisyUI for now)
  - `resources/assets/js/app.js` → `resources/js/app.js` (the existing form-guard JS)
  - The Tailwind `@source` glob in `app.css` is repointed to the views directory from the
    new location.
- **Layout swap** (`layouts/default.blade.php`, `layouts/auth.blade.php`): replace the
  `<link rel="stylesheet" href="{{ asset('css/app.css') }}">` and
  `<script src="{{ asset('js/app.js') }}">` tags with a single
  `@vite(['resources/css/app.css', 'resources/js/app.js'])` in `<head>`. `@cssbox` and the
  DaisyUI `data-theme` stay until #3.

### Transition safety

DaisyUI CSS continues to compile (now through Vite), so the welcome and login pages render
unchanged during the transition. No page markup changes in #2 beyond the asset-tag swap.

## Testing (framework Pest, `tests/View/ViteTest.php`)

Unit tests using a temp `public/` with fixture hot file / manifest, one behavior each:

1. **Dev mode**: hot file present → output contains `@vite/client` and a module tag at the dev URL for each entry.
2. **Build mode — CSS entry**: manifest maps `resources/css/app.css` to a hashed file → `<link rel="stylesheet" href="/build/...">`.
3. **Build mode — JS entry**: manifest maps `resources/js/app.js` → `<script type="module" src="/build/...">`.
4. **Build mode — JS entry with imported CSS**: chunk `css` array → additional `<link>` tags emitted.
5. **Missing manifest** in build mode → throws `\RuntimeException` naming the path.
6. **`@vite` directive** compiles to a `Vite` invocation (rendered against a fixture manifest produces the expected tags).
7. **`@viteReactRefresh`**: dev mode emits the refresh preamble; build mode emits empty string.

Target ≥80% coverage on `Phare\View\Vite`.

### Manual verification (app)
- `npm install` then `npm run build` produces `public/build/manifest.json`; loading a page
  emits hashed asset tags and the welcome/login pages render with DaisyUI intact.
- `npm run dev` writes `public/hot`; pages load from the dev server with HMR.

## Out of scope (separate specs)

- React, `@inertiajs/react`, `@vitejs/plugin-react`, `.tsx` entry, shadcn/ui (#3)
- DaisyUI removal, `@cssbox`/`data-theme` removal, Tailwind theme tokens (#3)
- Page ports to React (#4)
- Auth scaffolding (#5)
