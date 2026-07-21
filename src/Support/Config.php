<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Support;

final class Config
{
    public static function string(string $key, string $default): string
    {
        $value = config($key, $default);

        return is_string($value) ? $value : $default;
    }
}
