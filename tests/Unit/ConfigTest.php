<?php

declare(strict_types=1);

use Laltu\LaravelModular\Support\Config;

it('reads booleans with fallback', function () {
    config(['demo.flag' => true, 'demo.bad' => 'yes']);

    expect(Config::bool('demo.flag', false))->toBeTrue()
        ->and(Config::bool('demo.bad', false))->toBeFalse()
        ->and(Config::bool('demo.missing', true))->toBeTrue();
});

it('reads string lists with fallback', function () {
    config(['demo.list' => ['a', 1, 'b'], 'demo.bad' => 'x']);

    expect(Config::stringList('demo.list', []))->toBe(['a', 'b'])
        ->and(Config::stringList('demo.bad', ['d']))->toBe(['d']);
});
