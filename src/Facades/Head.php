<?php

declare(strict_types=1);

namespace Laravel\Head\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Laravel\Head\Head defaults(callable $callback)
 * @method static \Laravel\Head\Head errors(callable $callback)
 * @method static \Laravel\Head\Head status(int $status)
 * @method static \Laravel\Head\Head|\Laravel\Head\Title title(?string $title = null)
 * @method static \Laravel\Head\Head description(string $description)
 * @method static \Laravel\Head\Head|\Laravel\Head\Canonical canonical(?string $url = null)
 * @method static \Laravel\Head\OpenGraph og()
 * @method static \Laravel\Head\Twitter twitter()
 * @method static \Laravel\Head\Robots robots()
 * @method static \Laravel\Head\Head preload(string $href, ?string $as = null, bool|string|null $crossorigin = null, ?string $type = null, ?string $media = null)
 * @method static \Laravel\Head\Head prefetch(string $href, ?string $as = null)
 * @method static \Laravel\Head\Head preconnect(string $href, bool|string|null $crossorigin = null)
 * @method static \Laravel\Head\Head dnsPrefetch(string $href)
 * @method static \Laravel\Head\Head alternates(array<string, string> $alternates)
 * @method static \Laravel\Head\Head feed(string $href, string $title, string $type = 'rss')
 * @method static \Laravel\Head\Head schema(\Laravel\Head\Schema\SchemaObject|array<string, mixed> $schema)
 * @method static array<string, mixed> toArray(?int $status = null)
 * @method static string render(?int $status = null)
 * @method static \Laravel\Head\Head flush()
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
