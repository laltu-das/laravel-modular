# New Features

## Inertia.js Support

Module-aware Inertia rendering via `InertiaResponse` and `ModuleInertia` trait.

- `php artisan module:inertia {module}` - Generate scaffolding
- Middleware: `InertiaMiddleware`
- Controller trait: `ModuleInertia`
- Config: `laravel-modular.inertia.enabled`

## API Support

Standardized API responses and resources for modules.

- `ApiResponse` - Standard JSON responses (`success`, `message`, `data`)
- `ApiResource` - Base resource with module metadata
- `ApiModuleMiddleware` - Module-aware middleware
- Config: `laravel-modular.api.enabled`

## Extra Features

### Module Caching (`ModuleCache`)
- Scoped keys by module (`modular:{module}:{key}`)
- Command: `php artisan module:cache-flush {module}`

### Middleware Stacks (`ModuleMiddleware`)
- Register stacks per module
- Command: `php artisan module:middleware {module} {middleware...}`

### Broadcasting (`ModuleBroadcast`)
- Private/presence channels scoped to module
- Command: `php artisan module:broadcast {module}`

### Module Tests (`ModuleTest` trait)
- `ModuleTest` trait with `setUpModule()` and `moduleRequest()` helpers
