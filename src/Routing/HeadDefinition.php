<?php

declare(strict_types=1);

namespace Laravel\Head\Routing;

use Closure;
use Illuminate\Routing\Route;
use Laravel\Head\HeadData;

/**
 * @phpstan-type HeadArguments array<mixed, mixed>
 */
class HeadDefinition
{
    public const HEAD = 'laravel_head';

    public const GROUPS = 'laravel_head_groups';

    /**
     * @param  array<int|string, mixed>  $arguments
     * @return array<mixed, mixed>|Closure
     */
    public static function arguments(array $arguments): array|Closure
    {
        if (array_key_exists(0, $arguments) && $arguments[0] instanceof Closure) {
            return $arguments[0];
        }

        if (array_key_exists(0, $arguments) && is_array($arguments[0]) && count($arguments) === 1) {
            return $arguments[0];
        }

        return $arguments;
    }

    /**
     * @param  array<mixed, mixed>|Closure|null  $definition
     */
    public static function apply(HeadData $head, array|Closure|null $definition, ?Route $route = null): HeadData
    {
        if (is_null($definition)) {
            return $head;
        }

        if ($definition instanceof Closure) {
            $definition = $definition($route);
        }

        if ($definition instanceof HeadData) {
            return $head->merge($definition);
        }

        return is_array($definition) ? $head->fill(static::named($definition)) : $head;
    }

    /**
     * @return array<int, array<mixed, mixed>|Closure>
     */
    public static function groups(Route $route): array
    {
        $groups = $route->getAction(static::GROUPS) ?? [];

        if (! is_array($groups)) {
            return [];
        }

        return array_is_list($groups) ? $groups : [$groups];
    }

    /**
     * @param  array<mixed, mixed>  $definition
     * @return array<string, mixed>
     */
    protected static function named(array $definition): array
    {
        $named = [];

        foreach ($definition as $key => $value) {
            if (is_string($key)) {
                $named[$key] = $value;
            }
        }

        return $named;
    }
}
