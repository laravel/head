<?php

declare(strict_types=1);

namespace Laravel\Head\Routing;

use Closure;
use Illuminate\Routing\PendingResourceRegistration;
use Illuminate\Routing\PendingSingletonResourceRegistration;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteRegistrar;
use ReflectionProperty;

/**
 * Stores route-scoped head attributes.
 *
 * This is the only class that should know whether head data is stored in
 * Laravel route action keys or the framework route metadata bag.
 */
class RouteHeadRepository
{
    public const METADATA = 'metadata';

    public const HEAD = 'head';

    public const GROUPS = 'headGroups';

    /**
     * @param  array<mixed, mixed>|Closure  $attributes
     */
    public function put(Route $route, array|Closure $attributes): void
    {
        $route->action[static::METADATA] = $this->mergeMetadata(
            $route->action[static::METADATA] ?? [],
            [static::HEAD => $attributes],
        );
    }

    /**
     * @return array<mixed, mixed>|Closure|null
     */
    public function get(Route $route): array|Closure|null
    {
        $attributes = $this->getMetadata($route, static::HEAD);

        return is_array($attributes) || $attributes instanceof Closure ? $attributes : null;
    }

    /**
     * @param  array<mixed, mixed>|Closure  $head
     */
    public function pushGroup(RouteRegistrar $registrar, array|Closure $head): void
    {
        $attributes = $this->arrayProperty($registrar, 'attributes');
        $metadata = $this->metadataArray($attributes[static::METADATA] ?? []);
        $groups = $this->groupsArray($metadata[static::GROUPS] ?? []);
        $groups[] = $head;
        $metadata[static::GROUPS] = $groups;
        $attributes[static::METADATA] = $metadata;

        $this->setProperty($registrar, 'attributes', $attributes);
    }

    /**
     * @return array<int, array<mixed, mixed>|Closure>
     */
    public function groups(Route $route): array
    {
        return $this->groupsArray($this->getMetadata($route, static::GROUPS, []));
    }

    /**
     * @param  array<mixed, mixed>|Closure  $attributes
     */
    public function putPending(PendingResourceRegistration|PendingSingletonResourceRegistration $registration, array|Closure $attributes): void
    {
        $options = $this->arrayProperty($registration, 'options');
        $options[static::METADATA] = $this->mergeMetadata(
            $this->metadataArray($options[static::METADATA] ?? []),
            [static::HEAD => $attributes],
        );

        $this->setProperty($registration, 'options', $options);
    }

    /**
     * @param  array<string, mixed>  $action
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function addResourceAction(array $action, array $options): array
    {
        if (isset($options[static::METADATA]) && is_array($options[static::METADATA])) {
            $action[static::METADATA] = $this->mergeMetadata(
                $this->metadataArray($action[static::METADATA] ?? []),
                $options[static::METADATA],
            );
        }

        return $action;
    }

    protected function getMetadata(Route $route, ?string $key = null, mixed $default = null): mixed
    {
        $metadata = $route->getAction(static::METADATA) ?? [];

        if (is_null($key)) {
            return $metadata;
        }

        return data_get($metadata, $key, $default);
    }

    /**
     * @return array<mixed, mixed>
     */
    protected function metadataArray(mixed $metadata): array
    {
        return is_array($metadata) ? $metadata : [];
    }

    /**
     * @return array<int, array<mixed, mixed>|Closure>
     */
    protected function groupsArray(mixed $groups): array
    {
        if (! is_array($groups)) {
            return [];
        }

        return array_values(array_filter(
            $groups,
            fn (mixed $group): bool => is_array($group) || $group instanceof Closure
        ));
    }

    /**
     * @param  array<mixed, mixed>  ...$metadata
     * @return array<mixed, mixed>
     */
    protected function mergeMetadata(array ...$metadata): array
    {
        $merged = [];

        foreach ($metadata as $values) {
            foreach ($values as $key => $value) {
                if (array_key_exists($key, $merged) && $this->mergesMetadata($merged[$key], $value)) {
                    $value = $this->mergeMetadata($this->metadataArray($merged[$key]), $this->metadataArray($value));
                }

                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    protected function mergesMetadata(mixed $old, mixed $new): bool
    {
        return is_array($old)
            && is_array($new)
            && array_is_list($old) === false
            && array_is_list($new) === false;
    }

    /**
     * @return array<string, mixed>
     */
    protected function arrayProperty(object $target, string $property): array
    {
        $value = (new ReflectionProperty($target, $property))->getValue($target);

        return is_array($value) ? $value : [];
    }

    /**
     * @param  array<string, mixed>  $value
     */
    protected function setProperty(object $target, string $property, array $value): void
    {
        (new ReflectionProperty($target, $property))->setValue($target, $value);
    }
}
