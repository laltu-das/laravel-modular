---
name: laravel-modular-development
description: >
  Configure and apply the Laravel Modular package in Laravel applications.
license: MIT
metadata:
  author: laltu
---

# Laravel Modular

Use this skill when a Laravel application needs to organize feature code into independently discovered modules with the `laltu/laravel-modular` package.

## Primary goal

Use the package's public Artisan commands and configuration while keeping generated code in Laravel's familiar directories.

## Installation

```bash
composer require laltu/laravel-modular
php artisan vendor:publish --tag=laravel-modular-config
```

## Create a module

```bash
php artisan moduler:make-module School
php artisan moduler:list
```

A module is created below `Modules/School` by default. Its folders mirror the folders in a Laravel application, such as `Http/Controllers`, `Models`, `Providers`, `Jobs`, `Events`, `database`, `resources`, and `routes`.

## Generate code

Pass `--module` to any supported Laravel generator:

```bash
php artisan make:controller SchoolController --module=School
php artisan make:model School --module=School -m -f -s
php artisan make:request StoreSchoolRequest --module=School
```

Without `--module`, generators keep Laravel's standard behavior. Use the generated module's `Providers/ModuleServiceProvider.php` for module-specific bindings and boot logic.

## Module resources

The package automatically loads a module's `routes/web.php`, `routes/api.php`, migrations, views, translations, providers, commands, and listeners. Add or remove a module by creating or removing its directory below the configured module path; add `.disabled` to temporarily skip one.

## Rules

- Prefer Laravel's normal controllers, models, requests, jobs, events, listeners, and providers.
- Keep module-specific code in that module's generated Laravel-style directories.
- Use `module.php` for providers, commands, and event listener declarations.
- Do not depend on package internals when the public Artisan commands and configuration are sufficient.
