<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use LaravelModular\LaravelModular\LaravelModular;

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
        $this->artisan('make:module', ['name' => 'School'])
            ->expectsOutputToContain('Module [School] created successfully.')
            ->assertSuccessful();

        expect(File::exists($path.'/School/Http/Controllers'))
            ->toBeTrue()
            ->and(File::exists($path.'/School/Models'))
            ->toBeTrue()
            ->and(File::exists($path.'/School/config/school.php'))
            ->toBeTrue()
            ->and(File::exists($path.'/School/Providers/ModuleServiceProvider.php'))
            ->toBeTrue()
            ->and(File::get($path.'/School/Providers/ModuleServiceProvider.php'))
            ->toContain('namespace Modules\\School\\Providers;')
            ->and(File::get($path.'/School/module.php'))
            ->toContain('Modules\\School\\Providers\\ModuleServiceProvider::class');
    } finally {
        File::deleteDirectory($path);
    }
});
