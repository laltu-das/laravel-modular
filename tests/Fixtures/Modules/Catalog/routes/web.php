<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/catalog', fn (): string => 'catalog')->name('catalog.index');
