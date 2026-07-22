# Release Notes

## [Unreleased](https://github.com/laltu/laravel-modular/compare/v0.1.0...master)

### Added

- **Synchronous Communication (Method Calls)**: Modules can now communicate via direct method calls on public APIs (interfaces in `Contracts/`). New `ModuleApi` class with `LaravelModular::api()`, `apiFrom()`, `hasApi()`, `allApis()`, and `getProviderModule()` methods. New `module:apis` command to list public APIs.
- **Asynchronous Communication (Messaging)**: Message-based communication using Laravel's queue system as a message broker. New `Message` contract, `BaseMessage` base class, `MessageBus` for publishing/consuming, `MessageJob` for queue processing, and `message:consume` command for running consumers. New `make:message` generator command.
- Documentation guides for synchronous and asynchronous communication patterns.
- `Messages/` directory created by default in new modules.

### Fixed

- `composer lint:check` now actually verifies code style (`pint --test --parallel`) instead of rewriting files and always passing in CI.

### Removed

- Temporary PHPUnit debug extension (`GithubAnnotationExtension` / `GithubAnnotationTracer`) and its registration in `phpunit.xml.dist`.
- Debug output probe in the `test:unit` Composer script.
- Stale `/AGENTS_PACKAGE.md` entry in `.gitattributes`.

## [v0.1.0](https://github.com/laltu/laravel-modular/compare/...v0.1.0) - 202x-xx-xx

Initial pre-release.
