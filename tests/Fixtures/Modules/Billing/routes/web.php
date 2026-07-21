<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/billing', fn (): string => 'billing')->name('billing.index');
