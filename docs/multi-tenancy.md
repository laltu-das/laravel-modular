# Multi-tenancy

Two hooks integrate the package with your existing `TenantContext` patterns without coupling the package to any tenancy library — like Spring profiles, but per tenant.

## The two contracts

1. **`tenant_resolver`** — a class implementing `LaravelModular\LaravelModular\Contracts\TenantResolver`. It feeds `LaravelModular::tenant()` and the tenant payload of the `ModuleBooting` / `ModuleBooted` lifecycle events.
2. **`tenant_voter`** — a class implementing `LaravelModular\LaravelModular\Contracts\TenantModuleVoter`. It decides per tenant whether a module is active.

```php
// config/laravel-modular.php
'tenant_resolver' => App\Tenancy\CurrentTenantResolver::class,
'tenant_voter' => App\Tenancy\TenantModuleVoter::class,
```

```php
use LaravelModular\LaravelModular\Contracts\TenantResolver;
use LaravelModular\LaravelModular\Contracts\TenantModuleVoter;
use LaravelModular\LaravelModular\Support\Module;

final class CurrentTenantResolver implements TenantResolver
{
    public function current(): mixed
    {
        return app(TenantContext::class)->tenant();
    }
}

final class TenantModuleVoter implements TenantModuleVoter
{
    public function allows(Module $module, mixed $tenant): bool
    {
        // e.g. plan-based activation: tenant->plan includes the POS feature
        return in_array(strtolower($module->name), $tenant?->modules ?? [], true);
    }
}
```

Both are bound in the container by class name, so they can use constructor injection themselves. The resolver returns anything (`mixed`) — an Eloquent tenant model, a DTO, an ID — and the voter receives it back as the second argument.

## What the voter controls

A module the voter rejects is **not registered at all**: no providers, routes, listeners, or commands, and no `ModuleBooting` / `ModuleBooted` events for it. Because the decision is made during the container's registration phase, the rejected module is invisible to the whole boot process, exactly like a Spring bean filtered out by an inactive profile.

Combined with the `.disabled` marker, a module is fully active only when **both** conditions hold:

1. No `.disabled` file in the module directory, and
2. The voter (if any) allows it for the current tenant.

## Reading state at runtime

```php
use LaravelModular\LaravelModular\Facades\LaravelModular;

LaravelModular::modules();      // modules enabled for the current tenant
LaravelModular::moduleNames();  // ['Catalog', 'Billing', ...]
LaravelModular::has('Billing');
LaravelModular::module('Billing');        // throws ModuleNotFound if missing/disabled
LaravelModular::isEnabled($module);       // raw voter decision for a module
LaravelModular::tenant();                 // the resolved tenant (null without resolver)
```

All of these only expose modules enabled for the **current** tenant, so per-request tenant switching works as expected.

## Lifecycle events and tenancy

`ModuleBooting` / `ModuleBooted` are dispatched per enabled module during boot and carry the resolved tenant, letting your listeners do tenant-aware module initialization:

```php
use LaravelModular\LaravelModular\Events\ModuleBooted;

Event::listen(function (ModuleBooted $event): void {
    // $event->module and $event->tenant
});
```

## Support classes

`LaravelModular\LaravelModular\Support\CurrentTenant` is available from the container anywhere you cannot use the facade: `get()` returns the tenant (or `null`), `has()` reports whether a resolver is configured **and** returned a non-null tenant.
