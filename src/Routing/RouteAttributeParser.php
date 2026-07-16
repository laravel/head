<?php

declare(strict_types=1);

namespace Laravel\Head\Routing;

use Illuminate\Routing\Route;
use InvalidArgumentException;
use Laravel\Head\HeadData;
use Laravel\Head\TagRegistry;

/**
 * @phpstan-type HeadAttributeArray array<mixed, mixed>
 */
class RouteAttributeParser
{
    public const HEAD = 'head';

    protected static int $metadataLayer = 0;

    /**
     * Normalize variadic head arguments, unwrapping a single array argument.
     *
     * @param  array<int|string, mixed>  $arguments
     * @return array<mixed, mixed>
     */
    public static function arguments(array $arguments): array
    {
        if (array_key_exists(0, $arguments) && is_array($arguments[0]) && count($arguments) === 1) {
            return $arguments[0];
        }

        return $arguments;
    }

    /**
     * Wrap head attributes in a cache-friendly route metadata layer.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, array<string, array<string, mixed>>>
     */
    public static function metadata(array $attributes): array
    {
        if ($attributes === []) {
            return [];
        }

        return [
            static::HEAD => [
                'layer-'.static::$metadataLayer++ => $attributes,
            ],
        ];
    }

    /**
     * Wrap named head arguments in a cache-friendly route metadata layer,
     * discarding arguments that were not provided.
     *
     * @param  array<mixed, mixed>  $arguments
     * @return array<string, array<string, array<string, mixed>>>
     */
    public static function metadataFromArguments(array $arguments): array
    {
        $extensions = $arguments['extensions'] ?? [];

        unset($arguments['extensions']);

        if (is_array($extensions)) {
            $arguments += $extensions;
        }

        $attributes = [];

        foreach ($arguments as $key => $value) {
            if (is_string($key) && ! is_null($value)) {
                $attributes[$key] = $value;
            }
        }

        return static::metadata($attributes);
    }

    /**
     * Get the head attribute layers stored in the route's metadata.
     *
     * @return array<int, array<mixed, mixed>>
     */
    public static function routeMetadata(Route $route): array
    {
        $metadata = $route->getMetadata(static::HEAD, []);

        if (! is_array($metadata)) {
            return [];
        }

        $layers = [];

        foreach ($metadata as $attributes) {
            if (is_array($attributes)) {
                $layers[] = $attributes;
            }
        }

        return $layers;
    }

    /**
     * Apply head attributes on top of the given head data.
     *
     * @param  HeadData|array<mixed, mixed>|null  $attributes
     */
    public static function apply(HeadData $head, HeadData|array|null $attributes, TagRegistry $registry): HeadData
    {
        if (is_null($attributes)) {
            return $head;
        }

        if ($attributes instanceof HeadData) {
            return $head->merge($attributes);
        }

        return static::fill($head, static::named($attributes), $registry);
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
