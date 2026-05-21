<?php

declare(strict_types=1);

namespace Laravel\Head\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Laravel\Head\Head defaults(callable $callback)
 * @method static \Laravel\Head\Head errors(callable $callback)
 * @method static \Laravel\Head\Head status(int $status)
 * @method static \Laravel\Head\Head title(string $title, ?string $prefix = null, ?string $suffix = null, ?bool $bare = null)
 * @method static \Laravel\Head\Head description(string $description)
 * @method static \Laravel\Head\Head canonical(string|\Laravel\Head\CanonicalMode $value, ?bool $forceHttps = null, ?bool $trailingSlash = null)
 * @method static \Laravel\Head\Head robots(string $directives)
 * @method static \Laravel\Head\Head og(\Laravel\Head\OgType|string|null $type = null, ?string $title = null, ?string $description = null, ?string $url = null, ?string $image = null, ?string $video = null, ?string $audio = null, ?string $siteName = null, ?string $locale = null, ?string $determiner = null)
 * @method static \Laravel\Head\Head ogImage(string $url, ?string $alt = null, ?int $width = null, ?int $height = null, ?string $type = null, ?string $secureUrl = null)
 * @method static \Laravel\Head\Head ogVideo(string $url, ?string $alt = null, ?int $width = null, ?int $height = null, ?string $type = null, ?string $secureUrl = null)
 * @method static \Laravel\Head\Head ogAudio(string $url, ?string $type = null, ?string $secureUrl = null)
 * @method static \Laravel\Head\Head twitter(\Laravel\Head\TwitterCard|string|null $card = null, ?string $site = null, ?string $creator = null, ?string $title = null, ?string $description = null, ?string $image = null)
 * @method static \Laravel\Head\Head twitterImage(string $url, ?string $alt = null)
 * @method static \Laravel\Head\Head preload(string $href, ?string $as = null, bool|string|null $crossorigin = null, ?string $type = null, ?string $media = null)
 * @method static \Laravel\Head\Head prefetch(string $href, ?string $as = null)
 * @method static \Laravel\Head\Head preconnect(string $href, bool|string|null $crossorigin = null)
 * @method static \Laravel\Head\Head dnsPrefetch(string $href)
 * @method static \Laravel\Head\Head alternates(array<string, string> $alternates)
 * @method static \Laravel\Head\Head feed(string $href, string $title, string $type = 'rss')
 * @method static \Laravel\Head\Head meta(string $key, string $content, ?bool $property = null)
 * @method static \Laravel\Head\Head link(string $rel, string $href, array<string, bool|float|int|string|null> $attributes = [])
 * @method static \Laravel\Head\Head schema(\Laravel\Head\Schema\SchemaObject|array<string, mixed>|callable $schema)
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
