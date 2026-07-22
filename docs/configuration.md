# Configuration reference

Publish the configuration file to customize any option:

```bash
php artisan vendor:publish --tag=laravel-modular-config
```

All options live in `config/laravel-modular.php`. Every option is optional — the defaults are used when the file is not published.

## `enabled`

**Default:** `true`

Master switch for module discovery. When `false`, no module providers are registered and no modules boot (no routes, listeners, views, config, lifecycle events). The package's own Artisan commands (`module:make`, `module:list`, …) remain available.

## `path`

**Default:** `base_path('Modules')`

The directory that contains the modules. Every directory directly below it is a module.

## `namespace`

**Default:** `'Modules'`

The PSR-4 namespace prefix for modules. A module `Product` gets the namespace `Modules\Product`. The package registers this namespace at runtime, so no Composer autoload changes are needed. Change both `path` and `namespace` together (e.g. `path` → `app/Modules`, `namespace` → `App\Modules`).

## `tenant_resolver`

**Default:** `null`

Optional class name implementing `Laltu\Modular\Contracts\TenantResolver`. Feeds `LaravelModular::tenant()` and the tenant payload of the `ModuleBooting` / `ModuleBooted` events. See [multi-tenancy](multi-tenancy.md).

## `tenant_voter`

**Default:** `null`

Optional class name implementing `Laltu\Modular\Contracts\TenantModuleVoter`. Decides per tenant whether a module boots; rejected modules are not registered at all. See [multi-tenancy](multi-tenancy.md).

## `public_directories`

**Default:** `['Contracts', 'Events', 'Enums']`

Top-level module directories considered public API; everything else is internal by default. Used by [`module:boundaries`](module-boundaries.md).

## `auto_discovery`

Per-aspect switches for [auto-discovery](auto-discovery.md); all default to `true`:

| Key | Aspect |
|---|---|
| `config` | `{Module}/config/*.php` merged under the file name |
| `commands` | `{Module}/Console/Commands/*` registered in console |
| `listeners` | `{Module}/Listeners/*` wired from the `handle()` type-hint |
| `migrations` | `{Module}/database/migrations` |
| `observers` | `{Module}/Observers/{Model}Observer` ⇒ `Models/{Model}` |
| `policies` | `{Module}/Policies/{Model}Policy` ⇒ `Models/{Model}` |
| `providers` | `{Module}/Providers/*ServiceProvider` |
| `routes` | `{Module}/routes/web.php` and `routes/api.php` |
| `translations` | `{Module}/resources/lang` (namespaced by module) |
| `views` | `{Module}/resources/views` (namespaced by module) |

Explicit `module.php` manifest entries (`providers`, `commands`, `listeners`) are always registered, regardless of these toggles.

## Publishing tags

Both `laravel-modular` and `laravel-modular-config` publish the configuration file. The general tag is reserved for future publishable resources.
