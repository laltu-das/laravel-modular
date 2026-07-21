<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/catalog-status', fn (): string => 'ok')->name('catalog.status');
