<?php

declare(strict_types=1);

namespace Laltu\Modular\Test;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Testing\TestResponse;

/**
 * Test trait for modular applications.
 */
trait ModuleTest
{
    use LazilyRefreshDatabase;

    /**
     * Set up module-level database migrations.
     */
    protected function setUpModule(string $module): void
    {
        $basePath = rtrim(config('laravel-modular.path', base_path('Modules')), '/') . '/' . $module;
        $migrationsPath = $basePath . '/database/migrations';

        if (is_dir($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
        }

        $this->artisan('module:enable', ['name' => $module]);
    }

    /**
     * Make a module-scoped request.
     */
    protected function moduleRequest(string $method, string $uri, array $data = []): TestResponse
    {
        return $this->$method($uri, $data, [
            'X-Module' => $this->resolveModuleFromUri($uri),
        ]);
    }

    private function resolveModuleFromUri(string $uri): ?string
    {
        $parts = explode('/', $uri);
        $first = $parts[1] ?? null;
        return $first ? ucfirst($first) : null;
    }

    /**
     * Assert a module event was dispatched.
     */
    protected function assertModuleEventDispatched(string $eventClass, string $module): void
    {
        $this->assertDatabaseHas('events', function ($query) use ($eventClass, $module) {
            return $query->where('event_class', $eventClass)
                         ->where('module', $module);
        });
    }
}
