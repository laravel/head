<?php

declare(strict_types=1);

namespace Laravel\Head\Routing;

use Closure;
use Illuminate\Routing\Route;
use InvalidArgumentException;
use Laravel\Head\HeadData;
use Laravel\Head\MetadataRegistry;

/**
 * @phpstan-type HeadAttributeArray array<mixed, mixed>
 */
class AttributeParser
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
    public static function apply(HeadData $head, array|Closure|null $attributes, MetadataRegistry $metadata, ?Route $route = null): HeadData
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

        return is_array($attributes) ? static::fill($head, static::named($attributes), $metadata) : $head;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected static function fill(HeadData $head, array $attributes, MetadataRegistry $metadata): HeadData
    {
        $head = clone $head;

        $attributeKeys = $metadata->attributeKeys();

        foreach ($attributes as $key => $value) {
            if (! isset($attributeKeys[$key])) {
                throw new InvalidArgumentException(sprintf(
                    'Unknown route head attribute [%s]. Supported attributes are: %s.',
                    $key,
                    implode(', ', array_keys($attributeKeys)),
                ));
            }

            $metadata = $attributeKeys[$key]::fromAttributeValue($key, $value);

            if (! is_null($metadata)) {
                $head->set($metadata);
            }
        }

        return $head;
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
