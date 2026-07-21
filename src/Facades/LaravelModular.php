<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \LaravelModular\LaravelModular\LaravelModular
 */
class LaravelModular extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \LaravelModular\LaravelModular\LaravelModular::class;
    }
}
