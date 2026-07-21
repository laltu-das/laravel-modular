<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/legacy', fn (): string => 'legacy')->name('legacy.index');
