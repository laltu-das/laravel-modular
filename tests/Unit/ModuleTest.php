<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use LaravelModular\LaravelModular\Discovery\ModuleRepository;
use LaravelModular\LaravelModular\Support\Module;

it('resolves module-relative paths and classes', function () {
    $module = new Module('Shop', base_path('Modules/Shop'), 'Modules\\Shop');

    expect($module->class('Http/Controllers/HomeController'))->toBe('Modules\\Shop\\Http\\Controllers\\HomeController')
        ->and($module->path('routes/web.php'))->toContain('routes/web.php')
        ->and($module->has('routes/web.php'))->toBeFalse()
        ->and($module->manifest())->toBe([])
        ->and($module->disabled)->toBeFalse();
});

it('discovers modules and their disabled state', function () {
    $repository = new ModuleRepository(new Filesystem(), __DIR__.'/../Fixtures/Modules', 'Modules');

    expect(array_keys($repository->all()))->toBe(['Billing', 'Catalog'])
        ->and(array_keys($repository->all(true)))->toBe(['Billing', 'Catalog', 'Legacy'])
        ->and($repository->all(true)['Legacy']->disabled)->toBeTrue()
        ->and($repository->find('catalog'))->toBeInstanceOf(Module::class)
        ->and($repository->find('legacy'))->toBeNull();
});

it('reads module manifests', function () {
    $repository = new ModuleRepository(new Filesystem(), __DIR__.'/../Fixtures/Modules', 'Modules');

    expect($repository->find('Catalog')->manifest())->toBe(['name' => 'Catalog']);
});
