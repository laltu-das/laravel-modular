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

    public static function bool(string $key, bool $default): bool
    {
        $value = config($key, $default);

        return is_bool($value) ? $value : $default;
    }

    /**
     * @param  list<string>  $default
     * @return list<string>
     */
    public static function stringList(string $key, array $default): array
    {
        $value = config($key, $default);

        if (! is_array($value)) {
            return $default;
        }

        return array_values(array_filter($value, 'is_string'));
    }
}
