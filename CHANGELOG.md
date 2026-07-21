# Release Notes

## [Unreleased](https://github.com/laltu/laravel-modular/compare/v0.1.0...master)

### Added

- Documentation guides under `docs/` covering installation, creating modules, auto-discovery, module-aware generators, event-driven communication, module administration, multi-tenancy, module boundaries, and the configuration reference.
- README sections for requirements, documentation links, testing commands, changelog, contributing, security, and credits.

### Fixed

- `composer lint:check` now actually verifies code style (`pint --test --parallel`) instead of rewriting files and always passing in CI.

### Removed

- Temporary PHPUnit debug extension (`GithubAnnotationExtension` / `GithubAnnotationTracer`) and its registration in `phpunit.xml.dist`.
- Debug output probe in the `test:unit` Composer script.
- Stale `/AGENTS_PACKAGE.md` entry in `.gitattributes`.

## [v0.1.0](https://github.com/laltu/laravel-modular/compare/...v0.1.0) - 202x-xx-xx

Initial pre-release.
