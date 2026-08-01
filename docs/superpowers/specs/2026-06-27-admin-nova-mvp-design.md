# Phare Admin (Nova-lite) MVP — Design

Date: 2026-06-27
Status: Approved (design); plan pending

## Goal

Reproduce the core of Laravel Nova: a model-driven admin panel where each
model is registered as a *Resource* with declared *Fields*, and a generic
controller + generic React pages provide CRUD, search, and pagination — no
per-model controller or page files.

MVP targets ~80% of Nova's day-to-day value (Resource CRUD + Fields + search +
panel-level auth). Advanced Nova features are explicitly out of scope (see
"Out of scope").

## Repo split

Reusable engine lives in the framework package; only concrete resources live
in the app.

- **Framework** `/opt/framework/src/Phare/Admin/` — engine. English comments.
  Package version bump required to consume in app.
- **App** `/opt/phare/app/Admin/` — concrete resources (`PostResource`,
  `UserResource`). English comments.

Mirrors the existing `Phare\Inertia` / `Phare\Broadcasting` domain-dir +
ServiceProvider convention.

## Architecture

```
Phare\Admin\
  Resource                  abstract base: $model, $search, fields(), label(), uriKey()
  AdminManager              resource registry (uriKey -> Resource class), find/register/all
  AdminServiceProvider      binds AdminManager singleton, registers routes
  Fields\Field              abstract: name, label, rules, resolve(model), forDisplay(), forInput(), toArray()
  Fields\Text
  Fields\Textarea
  Fields\Number
  Fields\Boolean
  Fields\Select
  Fields\DateTime
  Fields\BelongsTo
  Http\ResourceController   generic CRUD; Inertia render for web, JSON for relation search
```

```
App\Admin\
  PostResource
  UserResource
```

### Resource base (responsibilities)
- Hold `static string $model`, `array $search` (searchable columns),
  `string $title` (column used as the human label when this resource is shown
  in a BelongsTo picker/cell; defaults to `'name'` if present else `'id'`).
- `fields(): array` — author-declared Field list.
- `uriKey(): string` — kebab/plural key from class name (e.g. `posts`),
  overridable.
- `label()` — display name.
- Query helpers: `newQuery()`, apply search `?q=` against `$search` columns,
  paginate.

### AdminManager (responsibilities)
- `register(string $resourceClass)` and `all()`.
- `find(string $uriKey): ?Resource` — 404 source when null.
- Populated from a config list (`config/admin.php` `resources` array) at boot.

## Routing & data flow

One generic controller, resource keyed in the URL (Nova-style). All routes
gated `['auth','verified','admin']` (reuses the existing `admin` middleware
already on `/admin/broadcasting`).

```
GET    /admin/resources/{resource}             index   (paginated list + search)
GET    /admin/resources/{resource}/create      create  (empty form)
POST   /admin/resources/{resource}             store
GET    /admin/resources/{resource}/{id}/edit   edit    (filled form)
PUT    /admin/resources/{resource}/{id}        update
DELETE /admin/resources/{resource}/{id}        destroy
GET    /admin/resources/{resource}/search?q=   relation search (JSON, for BelongsTo picker)
```

Flow:
1. Controller resolves `AdminManager::find({resource})` → Resource instance (404 if unknown).
2. Resource serializes `fields()` to JSON.
3. `Inertia::render('admin/resources/Index'|'Form', props)`.
4. Generic React pages render table/form from the field JSON — no per-resource
   React files.

## Fields

`Field::make('column')` fluent builder. Each field knows four things:
- **input rendering** — which React component + props.
- **table display** — `forDisplay()` formatted value.
- **validation** — `->rules('required','max:255')`.
- **value resolution** — `resolve(model)` pulls the attribute (or relation)
  off the model.

Serialized field JSON shape consumed by React:

```json
{ "component": "Text", "attribute": "title", "label": "Title",
  "value": "...", "options": null, "rules": ["required"], "readonly": false }
```

Field types (7):

| Field     | Input            | Notes |
|-----------|------------------|-------|
| Text      | text input       | default scalar |
| Textarea  | multiline        | body/description |
| Number    | number input     | int/float, cast-aware |
| Boolean   | switch/checkbox  | |
| Select    | dropdown         | `->options([...])` fixed list |
| DateTime  | date/datetime    | `->readonly()` for created_at/updated_at |
| BelongsTo | relation picker  | `->resource(UserResource::class)`; picker queries target resource's `search` endpoint with `?q=`; display label = target resource's `$title` column |

Example:

```php
// app/Admin/PostResource.php -- the post resource
class PostResource extends Resource {
    public static string $model = Post::class;
    public array $search = ['title'];

    public function fields(): array {
        return [
            Text::make('title')->rules('required', 'max:255'),
            Textarea::make('body'),
            BelongsTo::make('user')->resource(UserResource::class),
            DateTime::make('created_at')->readonly(),
        ];
    }
}
```

## Frontend

Generic Inertia pages under `resources/js/pages/admin/resources/`:

- **Index.tsx** — shadcn `Table`; search box (debounced → `?q=`); pagination;
  per-row edit/delete actions; "Create" button. Columns come from field JSON
  (`forDisplay` values).
- **Form.tsx** — renders inputs by switching on field `component`; shared by
  create and edit. Validation errors surfaced via the existing `errors` shared
  Inertia prop. Submit via Inertia `useForm`.
- **fields/** — small per-component React renderers (one file each), selected by
  a `component` switch. BelongsTo renderer hits the relation search endpoint.

Reuses the existing admin layout and shadcn `Table`/`Dialog`/`AlertDialog`/
`Select`/`Input`/`Textarea`/`Switch`.

Build gotcha (project): rebuild Vite after adding pages; Tailwind v4 JIT only
emits classes seen at build time.

## Error handling

- Unknown resource uriKey → HTTP 404.
- Validation failure → flash field errors via existing
  `Controller::backWithErrors`; Form.tsx shows them inline.
- Save failure → Phalcon ORM `exception_on_failed_save=true` throws; controller
  catches, flashes a friendly error, returns back.
- Delete → shadcn `AlertDialog` confirm before `DELETE`.
- BelongsTo search endpoint failure → empty options + logged warning (mirrors
  broadcasting monitor's resilient pattern).

## Testing

Project caveat: route middleware is a no-op in the Pest harness, and DB-write
flows segfault under the sqlite driver. So feature tests assert the rendered
Inertia component + props, not redirects or persisted writes.

- **Unit**: AdminManager register/find/uriKey resolution.
- **Unit**: Field serialization shape (`toArray`) for each of the 7 types.
- **Unit**: BelongsTo resolves the related label from a model instance.
- **Unit**: Resource search applies `?q=` to declared `$search` columns
  (assert query/builder state, no DB write).
- **Feature**: index route renders `admin/resources/Index` with the correct
  resource prop and serialized columns.
- **Feature**: edit route renders `admin/resources/Form` with field values.

Target: 80%+ on the engine. DB-write store/update/destroy paths verified
manually (per project caveat), not in the sqlite harness.

## Out of scope (add when a real screen needs it)

Actions, filters, lenses, metrics/cards, dashboards, soft-delete UI, CSV
export, file/image upload fields, per-resource authorization policies, custom
field types beyond the 7, inline/relation creation, global search across
resources.

## ponytail notes

- No per-resource controllers/pages — one generic controller + two generic
  React pages drive every resource. Add a bespoke screen only when a resource
  genuinely outgrows the generic form.
- Panel-level `admin` gate (all-or-nothing), not per-resource policies.
  Upgrade path: add `authorizedTo*` methods on Resource when a role needs
  partial access.
- Relation picker reuses the index/search endpoint rather than a separate API.
