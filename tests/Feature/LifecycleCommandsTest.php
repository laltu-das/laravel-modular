<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

it('lists discovered modules with their status', function () {
    $this->artisan('module:list')
        ->expectsOutputToContain('Billing')
        ->expectsOutputToContain('Catalog')
        ->expectsOutputToContain('Legacy')
        ->expectsOutputToContain('Disabled')
        ->expectsOutputToContain('Enabled')
        ->assertSuccessful();
});

it('disables and enables a module', function () {
    $path = storage_path('framework/testing/lifecycle-modules');
    File::deleteDirectory($path);
    File::ensureDirectoryExists($path.'/Toggle');
    config(['laravel-modular.path' => $path]);

    try {
        $this->artisan('module:disable', ['name' => 'Toggle'])
            ->expectsOutputToContain('Module [Toggle] disabled successfully.')
            ->assertSuccessful();

        expect(File::exists($path.'/Toggle/.disabled'))->toBeTrue();

        $this->artisan('module:disable', ['name' => 'Toggle'])
            ->expectsOutputToContain('Module [Toggle] is already disabled.')
            ->assertSuccessful();

        $this->artisan('module:enable', ['name' => 'Toggle'])
            ->expectsOutputToContain('Module [Toggle] enabled successfully.')
            ->assertSuccessful();

        expect(File::exists($path.'/Toggle/.disabled'))->toBeFalse();
    } finally {
        File::deleteDirectory($path);
    }
});

it('fails to toggle a module that does not exist', function () {
    $path = storage_path('framework/testing/lifecycle-modules');
    File::deleteDirectory($path);
    File::ensureDirectoryExists($path);
    config(['laravel-modular.path' => $path]);

    try {
        $this->artisan('module:disable', ['name' => 'Ghost'])
            ->expectsOutputToContain('Module [Ghost] does not exist.')
            ->assertFailed();
    } finally {
        File::deleteDirectory($path);
    }
});

it('scaffolds a module through the module:make and moduler:make-module aliases', function () {
    $path = storage_path('framework/testing/lifecycle-modules');
    File::deleteDirectory($path);
    File::ensureDirectoryExists($path);
    config(['laravel-modular.path' => $path]);

    try {
        $this->artisan('module:make', ['name' => 'Blog'])
            ->expectsOutputToContain('Module [Blog] created successfully.')
            ->assertSuccessful();

        $this->artisan('moduler:make-module', ['name' => 'Shop'])
            ->expectsOutputToContain('Module [Shop] created successfully.')
            ->assertSuccessful();

        expect(File::exists($path.'/Blog/Providers/ModuleServiceProvider.php'))->toBeTrue()
            ->and(File::exists($path.'/Blog/Listeners'))->toBeTrue()
            ->and(File::exists($path.'/Blog/Policies'))->toBeTrue()
            ->and(File::exists($path.'/Blog/config/blog.php'))->toBeTrue()
            ->and(File::exists($path.'/Shop/module.php'))->toBeTrue();
    } finally {
        File::deleteDirectory($path);
    }
});

it('refuses to overwrite an existing module without force', function () {
    $path = storage_path('framework/testing/lifecycle-modules');
    File::deleteDirectory($path);
    File::ensureDirectoryExists($path.'/Blog');
    config(['laravel-modular.path' => $path]);

    try {
        $this->artisan('module:make', ['name' => 'Blog'])
            ->expectsOutputToContain('Module [Blog] already exists.')
            ->assertFailed();
    } finally {
        File::deleteDirectory($path);
    }
});
