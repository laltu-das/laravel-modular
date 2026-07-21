# Release Notes

## [Unreleased](https://github.com/laltu-das/laravel-modular/compare/v0.1.0...master)

### Changed

- **Breaking:** the package root namespace moved from `LaravelModular\LaravelModular` to `Laltu\LaravelModular`, matching the `laltu/laravel-modular` Composer name. Update your `use` statements (facade alias `LaravelModular` and config key `laravel-modular` are unchanged).

### Added

- Documentation guides under `docs/` covering installation, creating modules, auto-discovery, module-aware generators, event-driven communication, module administration, multi-tenancy, module boundaries, and the configuration reference.
- Facade reference guide (`docs/facade-reference.md`) with an example per `LaravelModular` facade method.
- README sections for requirements, documentation links, testing commands, changelog, contributing, security, and credits.
- Regression coverage for the `module:make` scaffold: generated providers, manifests, route files, and config must never contain doubled namespace separators.
- Test coverage for `make:view --module=…` asserting the Blade extension is preserved.

### Fixed

- `make:view --module=…` now creates `*.blade.php` files (honoring `--extension`) instead of writing views with a `.php` extension.
- `composer lint:check` now actually verifies code style (`pint --test --parallel`) instead of rewriting files and always passing in CI.
- `composer.json` homepage and changelog compare links now point to `laltu-das/laravel-modular`.

### Removed

- Temporary PHPUnit debug extension (`GithubAnnotationExtension` / `GithubAnnotationTracer`) and its registration in `phpunit.xml.dist`.
- Debug output probe in the `test:unit` Composer script.
- Stale `/AGENTS_PACKAGE.md` entry in `.gitattributes`.

## [v0.1.0](https://github.com/laltu-das/laravel-modular/compare/...v0.1.0) - 202x-xx-xx

Initial pre-release.
