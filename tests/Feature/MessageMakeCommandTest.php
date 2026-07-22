<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;

beforeEach(function () {
    config(['laravel-modular.path' => base_path('Modules')]);
    // Ensure Modules directory exists
    (new Filesystem())->ensureDirectoryExists(base_path('Modules/TestModule'));
});

afterEach(function () {
    // Clean up
    (new Filesystem())->deleteDirectory(base_path('Modules/TestModule'));
});

it('creates a message in the Messages directory', function () {
    $this->artisan('make:message TestMessage --module=TestModule --channel=test')
        ->expectsOutputToContain('Message [TestMessage] created')
        ->assertSuccessful();

    expect(file_exists(base_path('Modules/TestModule/Messages/TestMessage.php')))->toBeTrue();
});

it('creates a message contract in the Contracts directory', function () {
    $this->artisan('make:message TestContract --module=TestModule --channel=test --contract')
        ->expectsOutputToContain('Message [TestContract] created')
        ->assertSuccessful();

    expect(file_exists(base_path('Modules/TestModule/Contracts/TestContract.php')))->toBeTrue();
});

it('fails if message already exists', function () {
    $this->artisan('make:message TestMessage --module=TestModule --channel=test')
        ->assertSuccessful();

    $this->artisan('make:message TestMessage --module=TestModule --channel=test')
        ->expectsOutputToContain('already exists')
        ->assertFailed();
});