<?php

declare(strict_types=1);

use Laltu\Modular\Api\ApiResponse;
use Laltu\Modular\Inertia\InertiaResponse;
use Laltu\Modular\Broadcasting\ModuleBroadcast;
use Laltu\Modular\Support\ModuleCache;
use Laltu\Modular\Support\ModuleMiddleware;

beforeEach(function () {
    $this->app->make('router')->get('/modular/api/status', function () {
        return ApiResponse::make()->message('ok')->success();
    });
});

test('api response creates standard payload', function () {
    $response = ApiResponse::make()->message('hello')->success(['data' => 'test']);
    expect($response->getData(true))->toMatchArray([
        'success' => true,
        'message' => 'hello',
        'data' => ['data' => 'test'],
    ]);
});

test('inertia response creates module aware component', function () {
    $res = new InertiaResponse('Billing');
    $res->component('Dashboard');
    $res->with(['key' => 'value']);

    expect($res)->toBeInstanceOf(InertiaResponse::class);
});

test('module middleware registers stacks', function () {
    $stack = new ModuleMiddleware();
    $stack->registerStack('Catalog', ['api', 'throttle']);
    expect($stack->stackFor('Catalog'))->toHaveCount(2);
});

test('module cache scopes keys by module', function () {
    $cache = new ModuleCache();
    $cache->forModule('Catalog');
    expect($cache)->toBeInstanceOf(ModuleCache::class);
});

test('module broadcast creates private channels', function () {
    $broadcast = new ModuleBroadcast('Orders');
    $channel = $broadcast->privateChannel('events');
    expect($channel)->toBeInstanceOf(\Illuminate\Broadcasting\PrivateChannel::class);
});
