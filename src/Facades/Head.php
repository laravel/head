<?php

declare(strict_types=1);

namespace Laravel\Head\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Laravel\Head\Head defaults(callable $callback)
 * @method static \Laravel\Head\Head title(?string $title = null)
 * @method static \Laravel\Head\Head description(?string $description = null)
 * @method static string render()
 *
 * @see \Laravel\Head\Head
 */
class Head extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'head';
    }
}
