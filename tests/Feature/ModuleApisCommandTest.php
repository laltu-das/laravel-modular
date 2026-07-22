<?php

declare(strict_types=1);

beforeEach(function () {
    config(['laravel-modular.path' => __DIR__.'/../Fixtures/Modules']);
});

it('lists all module APIs', function () {
    $this->artisan('module:apis')
        ->expectsOutputToContain('Orders')
        ->expectsOutputToContain('OrderGateway')
        ->assertSuccessful();
});

it('lists APIs for a specific module', function () {
    $this->artisan('module:apis', ['--module' => 'Orders'])
        ->expectsOutputToContain('OrderGateway')
        ->assertSuccessful();
});

it('fails for unknown module', function () {
    $this->artisan('module:apis', ['--module' => 'Unknown'])
        ->expectsOutputToContain('does not exist or is not enabled')
        ->assertFailed();
});