<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Laltu\Modular\Api\ApiResponse;

Route::prefix('modular')->group(function () {
    Route::get('api/status', function () {
        return ApiResponse::make()
            ->message('Module API is active')
            ->success([
                'version' => config('laravel-modular.api.version', 'v1'),
                'enabled' => config('laravel-modular.api.enabled', true),
            ]);
    })->name('modular.api.status');
});
