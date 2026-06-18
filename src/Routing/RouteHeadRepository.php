<?php

declare(strict_types=1);

namespace Laravel\Head\Routing;

use Closure;
use Illuminate\Routing\PendingResourceRegistration;
use Illuminate\Routing\PendingSingletonResourceRegistration;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteRegistrar;

/**
 * Stores route-scoped head attributes.
 */
class RouteHeadRepository
{
    public const HEAD = 'head';

    public const GROUPS = 'headGroups';

    protected int $group = 0;

    /**
     * @param  array<mixed, mixed>|Closure  $attributes
     */
    public function put(Route $route, array|Closure $attributes): void
    {
        $route->metadata([static::HEAD => $attributes]);
    }

    /**
     * @return array<mixed, mixed>|Closure|null
     */
    public function get(Route $route): array|Closure|null
    {
        $attributes = $route->getMetadata(static::HEAD);

        return is_array($attributes) || $attributes instanceof Closure ? $attributes : null;
    }

    /**
     * @param  array<mixed, mixed>|Closure  $head
     */
    public function pushGroup(RouteRegistrar $registrar, array|Closure $head): void
    {
        $registrar->metadata([static::GROUPS => ['group-'.$this->group++ => $head]]);
    }

    /**
     * @return array<int, array<mixed, mixed>|Closure>
     */
    public function groups(Route $route): array
    {
        return $this->groupsArray($route->getMetadata(static::GROUPS, []));
    }

    /**
     * @param  array<mixed, mixed>|Closure  $attributes
     */
    public function putPending(PendingResourceRegistration|PendingSingletonResourceRegistration $registration, array|Closure $attributes): void
    {
        $registration->metadata([static::HEAD => $attributes]);
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
}
