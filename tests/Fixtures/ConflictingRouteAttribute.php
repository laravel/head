<?php

declare(strict_types=1);

namespace Laravel\Head\Tests\Fixtures;

use Laravel\Head\Tags\TagBuilder;

/**
 * @phpstan-consistent-constructor
 */
class ConflictingRouteAttribute extends TagBuilder
{
    public static function key(): string
    {
        return 'conflictingRouteAttribute';
    }

    public static function routeAttributeKeys(): array
    {
        return ['title'];
    }

    public function overlayOn(?TagBuilder $base): static
    {
        return $this;
    }

    public function isEmpty(): bool
    {
        return true;
    }
}
