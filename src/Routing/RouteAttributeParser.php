<?php

declare(strict_types=1);

namespace Laravel\Head\Routing;

use Closure;
use Illuminate\Routing\Route;
use InvalidArgumentException;
use Laravel\Head\HeadData;
use Laravel\Head\TagRegistry;

/**
 * @phpstan-type HeadAttributeArray array<mixed, mixed>
 */
class RouteAttributeParser
{
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
     * @param  array<mixed, mixed>|Closure|null  $attributes
     */
    public static function apply(HeadData $head, array|Closure|null $attributes, TagRegistry $registry, ?Route $route = null): HeadData
    {
        if (is_null($attributes)) {
            return $head;
        }

        if ($attributes instanceof Closure) {
            $attributes = $attributes($route);
        }

        if ($attributes instanceof HeadData) {
            return $head->merge($attributes);
        }

        return is_array($attributes) ? static::fill($head, static::named($attributes), $registry) : $head;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected static function fill(HeadData $head, array $attributes, TagRegistry $registry): HeadData
    {
        $head = clone $head;

        $routeAttributeKeys = $registry->routeAttributeKeys();

        foreach ($attributes as $key => $value) {
            if (! isset($routeAttributeKeys[$key])) {
                throw new InvalidArgumentException(sprintf(
                    'Unknown route head attribute [%s]. Supported attributes are: %s.',
                    $key,
                    implode(', ', array_keys($routeAttributeKeys)),
                ));
            }

            $builderClass = $routeAttributeKeys[$key];

            $builder = $builderClass::fromRouteAttribute($key, $value);

            if (is_null($builder) || ($builder->isEmpty() && ! static::isEmptyRouteAttributeValue($value))) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid value for route head attribute [%s].',
                    $key,
                ));
            }

            $head->overlayBuilder($builder);
        }

        return $head;
    }

    protected static function isEmptyRouteAttributeValue(mixed $value): bool
    {
        return $value === [];
    }

    /**
     * @param  array<mixed, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected static function named(array $attributes): array
    {
        $named = [];

        foreach ($attributes as $key => $value) {
            if (is_string($key)) {
                $named[$key] = $value;

                continue;
            }

            throw new InvalidArgumentException('Route head attributes must be named.');
        }

        return $named;
    }
}
