---
name: laravel-modular-development
description: >
  Configure and apply the Laravel Modular package in Laravel applications.
license: MIT
metadata:
  author: laltu
---

# Laravel Modular

Use this skill when a Laravel application needs to organize feature code into independently discovered modules with the `laltu/laravel-modular` package. The package works like a Spring Boot Modulith: package-by-feature organization, auto-discovery, event-driven communication, per-tenant activation, and verifiable boundaries.

## Primary goal

Use the package's public Artisan commands and configuration while keeping generated code in Laravel's familiar directories.

## Installation

```bash
composer require laltu/laravel-modular
php artisan vendor:publish --tag=laravel-modular-config
```

## Create a module

```bash
php artisan module:make Product
# aliases: make:module, moduler:make-module
php artisan module:list
```

A module is created below `Modules/Product` by default. Its folders mirror the folders in a Laravel application, such as `Http/Controllers`, `Models`, `Providers`, `Listeners`, `Jobs`, `Events`, `config`, `database`, `resources`, and `routes`.

## Generate code

Pass `--module` to any supported Laravel generator:

```bash
php artisan make:controller ProductController --module=Product
php artisan make:model Product --module=Product -m -f -s
php artisan make:request StoreProductRequest --module=Product
```

Without `--module`, generators keep Laravel's standard behavior. Use the generated module's `Providers/ModuleServiceProvider.php` for module-specific bindings and boot logic.

## Auto-discovery

For each enabled module the package automatically loads: `config/*.php` (merged by file name), `Providers/*ServiceProvider.php` (full register+boot lifecycle), `Console/Commands/*Command.php`, `Listeners/*.php` (wired from the `handle()` type-hint), `Policies/{Model}Policy.php`, `Observers/{Model}Observer.php`, `routes/web.php` and `routes/api.php`, migrations, namespaced views and translations. Toggle each aspect under `auto_discovery` in `config/laravel-modular.php`. `module.php` manifest entries (providers, commands, listeners — wildcards allowed) are merged with auto-discovered ones.

Add or remove a module by creating or removing its directory below the configured module path; use `php artisan module:disable <name>` / `php artisan module:enable <name>` (or a `.disabled` file) to temporarily skip one.

## Event-driven communication

Modules communicate through events, not through each other's internals. Public events live in `{Module}/Events/`; listeners live in the consuming module's `Listeners/`. Publish with `LaravelModular::publish($event)` (or `event()`); subscribe at runtime with `LaravelModular::listen($event, $listener)` including wildcards like `Modules\Billing\Events\*`.

## Multi-tenancy

Set `tenant_resolver` (a `TenantResolver` implementation around your TenantContext) and optionally `tenant_voter` (a `TenantModuleVoter` implementation) to activate modules per tenant — like Spring profiles. `LaravelModular::modules()` then exposes only modules enabled for the current tenant, and lifecycle events `ModuleBooting`/`ModuleBooted` carry the tenant.

## Boundaries

A module's public API is its top-level `Contracts/`, `Events/`, and `Enums/` directories (see `public_directories`); every other top-level directory is internal. Run `php artisan module:boundaries` (optionally `--module=Name`) to find cross-module references to internal classes; the command exits non-zero on violations, so it belongs in CI.

## Rules

- Prefer Laravel's normal controllers, models, requests, jobs, events, listeners, and providers.
- Keep module-specific code in that module's generated Laravel-style directories.
- Cross-module usage: only another module's public directories (`Contracts`, `Events`, `Enums` by default); communicate through events for behavior.
- Use `module.php` for declarations auto-discovery does not cover.
- Do not depend on package internals when the public Artisan commands and configuration are sufficient.
