# Laravel Modular documentation

A Spring Boot Modulith-style modular monolith for Laravel: package-by-feature organization, convention-based auto-discovery, low-coupling event-driven communication, per-tenant module activation, and verifiable module boundaries.

## Guides

1. [Installation & setup](installation.md) — requirements, installation, publishing the config, verifying the setup
2. [Creating modules](creating-modules.md) — `module:make`, the module directory layout, and the `module.php` manifest
3. [Auto-discovery](auto-discovery.md) — what is discovered automatically and how to toggle each aspect
4. [Generating resources in a module](generating-resources.md) — the `--module` option on every Laravel generator
5. [Event-driven communication](event-driven-communication.md) — publishing, listening, wildcard listeners, runtime subscriptions
6. [Module administration](module-administration.md) — `module:list`, `module:enable`, `module:disable`, and lifecycle events
7. [Multi-tenancy](multi-tenancy.md) — `TenantResolver` and `TenantModuleVoter` for per-tenant module activation
8. [Module boundaries](module-boundaries.md) — public API by convention, `module:boundaries` verification, fixing violations
9. [Configuration reference](configuration.md) — every option in `config/laravel-modular.php`
10. [Facade reference](facade-reference.md) — every `LaravelModular` facade method, support classes, and lifecycle events

## Command overview

| Command | Purpose |
|---|---|
| `module:make {name} {--force}` | Scaffold a new module (aliases `make:module`, `moduler:make-module`) |
| `module:list` | List modules and their Enabled/Disabled status |
| `module:enable {name}` | Remove a module's `.disabled` marker |
| `module:disable {name}` | Disable a module without deleting its code |
| `module:boundaries {--module=}` | Report cross-module references to internal code |
| `make:* --module={name}` | Generate Laravel resources inside a module |

## Quick start

```bash
composer require laltu/laravel-modular
php artisan module:make Billing
php artisan make:controller InvoiceController --module=Billing
php artisan module:list

php artisan tinker
>>> LaravelModular::moduleNames();
=> ["Billing"]
```

```php
// Every Feature <-> Feature conversation goes through events:
use Laltu\LaravelModular\Facades\LaravelModular;

LaravelModular::publish(new Modules\Billing\Events\InvoicePaid(amount: 2500));
```
