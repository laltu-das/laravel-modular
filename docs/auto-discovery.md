# Auto-discovery

Convention over configuration, like Spring Boot's classpath scanning: for every enabled module, Laravel Modular finds the classes below conventional directories and wires them into the framework. There is no registration boilerplate.

## What is discovered

| Convention | What happens |
|---|---|
| `config/*.php` | merged into the config repository, keyed by file name (`config/billing.php` → `config('billing.*')`) |
| `Providers/*ServiceProvider.php` | registered with the full provider lifecycle (`register` + `boot`) |
| `Console/Commands/*Command.php` | registered as Artisan commands (console only) |
| `Listeners/*.php` | event inferred from the `handle()` / `__invoke()` type-hint, then subscribed |
| `Policies/{Model}Policy.php` | `Gate::policy(Models\{Model}, {Model}Policy)` |
| `Observers/{Model}Observer.php` | `Models\{Model}::observe({Model}Observer)` |
| `routes/web.php`, `routes/api.php` | loaded as module routes |
| `database/migrations` | registered with the migrator |
| `resources/views`, `resources/lang` | loaded namespaced by module (`product::welcome`, `product::messages.*`) |

Discovery details:

- Only **concrete, instantiable** classes are picked up; abstract classes and interfaces are ignored.
- Module service providers are registered during the container's **registration phase**, so they behave exactly like any other provider — their `register()` bindings are available to other providers and their own `boot()` runs.
- Listeners in `Listeners/` whose `handle()` method has no class type-hint are skipped; declare them in the `module.php` manifest instead if you want to wire them manually.
- Policies and observers follow the strict `{Model}Policy` / `{Model}Observer` naming convention: `Policies/ProductPolicy.php` maps to `Models/Product.php`.

## Disabling aspects individually

Every aspect can be turned off in `config/laravel-modular.php`:

```php
'auto_discovery' => [
    'config' => true,        // {Module}/config/*.php merged under the file name
    'commands' => true,      // {Module}/Console/Commands/* registered in console
    'listeners' => true,     // {Module}/Listeners/* wired from the handle() type-hint
    'migrations' => true,    // {Module}/database/migrations
    'observers' => true,     // {Module}/Observers/{Model}Observer => Models/{Model}
    'policies' => true,      // {Module}/Policies/{Model}Policy => Models/{Model}
    'providers' => true,     // {Module}/Providers/*ServiceProvider
    'routes' => true,        // {Module}/routes/web.php and routes/api.php
    'translations' => true,  // {Module}/resources/lang (namespaced by module)
    'views' => true,         // {Module}/resources/views (namespaced by module)
],
```

Turning an aspect off stops **convention discovery** for it. Explicit `module.php` manifest entries are unaffected: manifest `providers`, `commands`, and `listeners` are always registered, no matter the toggle.

## Manifest merging

The `module.php` manifest and auto-discovery are complementary:

- `providers`: manifest entries first, then discovered `*ServiceProvider` classes; duplicates removed.
- `commands`: manifest entries plus discovered `Console/Commands/*` classes; duplicates removed.
- `listeners`: manifest entries (explicit events and wildcard patterns) plus discovered listeners merged per event.

## Disabling modules

A module containing a `.disabled` marker file is never discovered (see [module administration](module-administration.md)), and with a [tenant voter](multi-tenancy.md) configured, rejected modules are not registered at all — no providers, routes, listeners, or commands.

## Lifecycle events

When the application boots, `Laltu\Modular\Events\ModuleBooting` fires before each enabled module boots and `ModuleBooted` fires after. Both carry the module and the current tenant resolved by the configured [tenant resolver](multi-tenancy.md).
