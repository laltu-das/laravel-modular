<?php

declare(strict_types=1);

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
