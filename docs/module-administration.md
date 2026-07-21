# Module administration

Commands for inspecting and toggling modules at runtime.

## Listing modules

```bash
php artisan module:list
```

Shows every directory below the module path — including disabled ones — with its `Enabled` / `Disabled` status and path.

## Disabling a module

```bash
php artisan module:disable Billing
```

Creates an empty `.disabled` marker file inside the module. A disabled module is skipped entirely: no providers, routes, listeners, commands, config, views, translations, or migrations are loaded for it, and it no longer shows up through `LaravelModular::modules()`, `moduleNames()`, `has()`, or `module()`.

Disabling is **non-destructive**: the code stays on disk and autoloadable, ready to be re-enabled. Running the command again reports `Module [Billing] is already disabled.`; running it for a missing module fails with `Module [Billing] does not exist.`

After the marker is created, a `Laltu\LaravelModular\Events\ModuleDisabled` event is dispatched carrying the module name.

## Enabling a module

```bash
php artisan module:enable Billing
```

Removes the `.disabled` marker (reporting `Module [Billing] is already enabled.` when there is nothing to remove) and dispatches `Laltu\LaravelModular\Events\ModuleEnabled`.

Both commands flush the package's module cache, so they always reflect the on-disk state.

## Reacting to lifecycle events

All four lifecycle events are regular Laravel events you can listen to:

```php
use Illuminate\Support\Facades\Event;
use Laltu\LaravelModular\Events\ModuleBooted;
use Laltu\LaravelModular\Events\ModuleBooting;
use Laltu\LaravelModular\Events\ModuleDisabled;
use Laltu\LaravelModular\Events\ModuleEnabled;

Event::listen(function (ModuleBooting $event): void {
    // fires per enabled module before it boots; $event->module, $event->tenant
});

Event::listen(function (ModuleBooted $event): void {
    // module fully booted (providers, routes, listeners, config, views...)
});

Event::listen(function (ModuleDisabled $event): void {
    Cache::forget('enabled_modules');   // $event->name — 'Billing'
});

Event::listen(function (ModuleEnabled $event): void {
    Cache::forget('enabled_modules');
});
```

A practical use: warm per-module settings into cache when a module boots, or clear feature caches when one is toggled.

## Verifying boundaries

```bash
php artisan module:boundaries
```

See [module boundaries](module-boundaries.md) for details — exits non-zero on violations, so it fits neatly into CI.
