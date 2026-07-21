<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Laltu\LaravelModular\LaravelModular;

it('resolves the singleton', function () {
    expect(app(LaravelModular::class))->toBeInstanceOf(LaravelModular::class);
});

it('returns the same instance from the container', function () {
    expect(app(LaravelModular::class))->toBe(app(LaravelModular::class));
});

it('merges the package config', function () {
    expect(config('laravel-modular.placeholder'))->toBe('default');
});

it('loads the package translations', function () {
    expect(trans('laravel-modular::messages.placeholder'))->toBe('LaravelModular placeholder translation.');
});

it('loads the package views', function () {
    expect(view()->exists('laravel-modular::placeholder'))->toBeTrue();
});

it('registers the artisan command', function () {
    $this->artisan('laravel-modular:placeholder')
        ->expectsOutputToContain('LaravelModular placeholder command executed.')
        ->assertSuccessful();
});

it('uses convention-first module defaults', function () {
    expect(config('laravel-modular.namespace'))->toBe('Modules')
        ->and(config('laravel-modular.enabled'))->toBeTrue()
        ->and(config('laravel-modular.tenant_resolver'))->toBeNull()
        ->and(config('laravel-modular.tenant_voter'))->toBeNull()
        ->and(config('laravel-modular.public_directories'))->toBe(['Contracts', 'Events', 'Enums'])
        ->and(config('laravel-modular.auto_discovery.listeners'))->toBeTrue()
        ->and(config('laravel-modular.auto_discovery.routes'))->toBeTrue();
});

it('creates a module using Laravel directories', function () {
    $path = storage_path('framework/testing/modules');
    File::deleteDirectory($path);
    config(['laravel-modular.path' => $path]);

    try {
        $this->artisan('make:module', ['name' => 'Product'])
            ->expectsOutputToContain('Module [Product] created successfully.')
            ->assertSuccessful();

        expect(File::exists($path.'/Product/Http/Controllers'))
            ->toBeTrue()
            ->and(File::exists($path.'/Product/Models'))
            ->toBeTrue()
            ->and(File::exists($path.'/Product/config/product.php'))
            ->toBeTrue()
            ->and(File::exists($path.'/Product/Providers/ModuleServiceProvider.php'))
            ->toBeTrue()
            ->and(File::get($path.'/Product/Providers/ModuleServiceProvider.php'))
            ->toContain('namespace Modules\\Product\\Providers;')
            ->and(File::get($path.'/Product/module.php'))
            ->toContain('Modules\\Product\\Providers\\ModuleServiceProvider::class');
    } finally {
        File::deleteDirectory($path);
    }
});

it('scaffolds a module whose generated files use valid namespace separators', function () {
    $path = storage_path('framework/testing/modules');
    File::deleteDirectory($path);
    config(['laravel-modular.path' => $path]);

    try {
        $this->artisan('make:module', ['name' => 'Blog'])->assertSuccessful();

        $files = [
            $path.'/Blog/Providers/ModuleServiceProvider.php',
            $path.'/Blog/module.php',
            $path.'/Blog/routes/web.php',
            $path.'/Blog/routes/api.php',
            $path.'/Blog/config/blog.php',
        ];

        foreach ($files as $file) {
            $contents = File::get($file);

            // A doubled separator outside of a string literal would be a
            // parse error, so generated files must always be single-backslash.
            expect(str_contains($contents, '\\\\'))->toBeFalse("Doubled namespace separator found in [{$file}]")
                ->and(str_starts_with($contents, '<?php'))->toBeTrue();
        }

        expect(File::get($path.'/Blog/Providers/ModuleServiceProvider.php'))
            ->toContain('namespace Modules\\Blog\\Providers;')
            ->and(File::get($path.'/Blog/module.php'))
            ->toContain('Modules\\Blog\\Providers\\ModuleServiceProvider::class')
            ->and(File::get($path.'/Blog/routes/web.php'))
            ->toContain('use Illuminate\\Support\\Facades\\Route;');
    } finally {
        File::deleteDirectory($path);
    }
});

it('generates module views with the blade extension', function () {
    $path = storage_path('framework/testing/modules');
    File::deleteDirectory($path);
    config(['laravel-modular.path' => $path]);

    try {
        $this->artisan('make:module', ['name' => 'Blog'])->assertSuccessful();

        $this->artisan('make:view', ['name' => 'welcome', '--module' => 'Blog'])
            ->assertSuccessful();

        expect(File::exists($path.'/Blog/resources/views/welcome.blade.php'))->toBeTrue()
            ->and(File::exists($path.'/Blog/resources/views/welcome.php'))->toBeFalse();
    } finally {
        File::deleteDirectory($path);
    }
});
