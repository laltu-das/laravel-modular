<?php

declare(strict_types=1);

beforeEach(function () {
    config(['laravel-modular.path' => __DIR__.'/../Fixtures/BoundaryModules']);
});

it('reports references to another module internals', function () {
    $this->artisan('module:boundaries')
        ->expectsOutputToContain('Module boundary violations detected')
        ->expectsOutputToContain('IncomeLedger')
        ->expectsOutputToContain('Reporting')
        ->doesntExpectOutputToContain('InvoiceGateway')
        ->assertFailed();
});

it('passes modules that only use public APIs', function () {
    $this->artisan('module:boundaries', ['--module' => 'Invoicing'])
        ->expectsOutputToContain('All module boundaries respected.')
        ->assertSuccessful();
});

it('fails for an unknown module filter', function () {
    $this->artisan('module:boundaries', ['--module' => 'Unknown'])
        ->expectsOutputToContain('does not exist or is disabled')
        ->assertFailed();
});
