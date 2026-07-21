# Generating resources in a module

Every Laravel generator gains a `--module` option. Pass it and the class is placed inside the module, with the module's namespace — without `--module`, Laravel's normal generator behavior is completely unchanged.

```bash
php artisan make:controller CheckoutController --module=Billing
# Modules/Billing/Http/Controllers/CheckoutController.php
# namespace Modules\Billing\Http\Controllers;
```

## Supported generators

| Command | Module directory |
|---|---|
| `make:cast` | `Casts` |
| `make:channel` | `Broadcasting` |
| `make:command` | `Console/Commands` |
| `make:component` | `View/Components` |
| `make:controller` | `Http/Controllers` |
| `make:enum` | `Enums` |
| `make:event` | `Events` |
| `make:exception` | `Exceptions` |
| `make:factory` | `database/factories` |
| `make:interface` | `Contracts` |
| `make:job` | `Jobs` |
| `make:listener` | `Listeners` |
| `make:mail` | `Mail` |
| `make:middleware` | `Http/Middleware` |
| `make:model` | `Models` |
| `make:notification` | `Notifications` |
| `make:observer` | `Observers` |
| `make:policy` | `Policies` |
| `make:provider` | `Providers` |
| `make:request` | `Http/Requests` |
| `make:resource` | `Http/Resources` |
| `make:rule` | `Rules` |
| `make:scope` | `Models/Scopes` |
| `make:seeder` | `database/seeders` |
| `make:test` | `tests` |
| `make:view` | `resources/views` |

The module must exist first; otherwise the generator fails with `Module [X] does not exist. Run make:module first.`

## Model companions stay inside the module

`make:model` honors `-m` (migration), `-f` (factory), and `-s` (seeder) — but instead of writing to the application's global `database/` directory, companions are created inside the module:

```bash
php artisan make:model Invoice --module=Billing -m -f -s
```

```text
Modules/Billing/
├── Models/Invoice.php                    Modules\Billing\Models\Invoice
├── database/
│   ├── factories/InvoiceFactory.php      Modules\Billing\Database\Factories\InvoiceFactory
│   ├── migrations/xxxx_create_invoices_table.php
│   └── seeders/InvoiceSeeder.php         Modules\Billing\Database\Seeders\InvoiceSeeder
```

Factory and seeder namespaces resolve out of the box because the package registers `{Module}\Database\Factories\` and `{Module}\Database\Seeders\` autoload paths for every module.

## Full example

```bash
php artisan module:make Category
php artisan make:controller CategoryController --module=Category
php artisan make:model Category --module=Category -m -f -s
php artisan make:request CreateCategoryRequest --module=Category
php artisan make:job ProcessCategoryImport --module=Category
php artisan make:event CategoryCreated --module=Category
php artisan make:policy CategoryPolicy --module=Category
php artisan make:resource CategoryResource --module=Category
php artisan make:middleware EnsureCategoryActive --module=Category
php artisan make:observer CategoryObserver --module=Category
php artisan make:mail CategorySummaryMail --module=Category
```

Generated `Console/Commands`, `Listeners`, `Policies`, `Observers`, and `Providers` are picked up by [auto-discovery](auto-discovery.md) on the next request — zero wiring needed.
