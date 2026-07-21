# Module boundaries

Spring Modulith `ApplicationModules::verify()` for Laravel: convention-based verification that modules only touch each other's **public API**.

## The convention

- A module's **public API** = its top-level `Contracts/`, `Events/`, and `Enums/` directories (configurable via `public_directories`).
- **Everything else is internal by default.** Other modules must not reference it — keep controllers, models, jobs, listeners, helpers, and `Internal/` implementation details private to their module.

The right coupling looks like this: `Reporting` may listen to `Modules\Invoicing\Events\InvoiceIssued` or depend on `Modules\Invoicing\Contracts\InvoiceGateway`, but it may never reach into `Modules\Invoicing\Internal\Ledger\IncomeLedger`.

## Verifying

```bash
php artisan module:boundaries                # inspect all enabled modules
php artisan module:boundaries --module=Billing
```

The command exits **non-zero** on violations, so it is CI-friendly:

```text
Module boundary violations detected (1):
+-----------+----------------------------+----------------------------------------------+
| Module    | File                       | References                                   |
+-----------+----------------------------+----------------------------------------------+
| Reporting | Jobs/RecalculateLedger.php | Modules\Invoicing\Internal\Ledger\IncomeLedger |
+-----------+----------------------------+----------------------------------------------+
```

When everything is clean: `All module boundaries respected.`

Passing an unknown (or disabled) module name to `--module` fails with `Module [X] does not exist or is disabled.`

Notes:

- References from a module to **itself** are always allowed.
- Only **enabled** modules are inspected; disabled modules are skipped.
- The inspector scans every `.php` file in each module for references to other modules' namespaces (`Modules\<Module>\<TopLevelDirectory>\...`).

## Widening a module's public API

Expose more of a module deliberately by adding directories to `public_directories` in `config/laravel-modular.php`:

```php
// config/laravel-modular.php
'public_directories' => ['Contracts', 'Events', 'Enums'],
```

Add `Http` to let other modules route to its controllers, for example, or `Models` when a shared read model is intentional. The alternative — usually the better one — is to move shared code into `Contracts/`, `Events/`, or `Enums/` so the public surface stays explicit and small.

## Fixing violations

When the report flags a reference:

1. Move the referenced class into one of the module's public directories, ideally behind an interface in `Contracts/`.
2. Or replace the direct dependency with [an event](event-driven-communication.md): publish from the producer, listen from the consumer, and let the reference disappear entirely.
