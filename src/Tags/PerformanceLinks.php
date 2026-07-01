<?php

declare(strict_types=1);

namespace Laravel\Head\Tags;

use Laravel\Head\Enums\ImageType;
use Laravel\Head\Rendering\ResolvedHead;
use Laravel\Head\Rendering\TagRenderer;

/**
 * @phpstan-consistent-constructor
 *
 * @phpstan-type LinkAttributes array{href: string, as?: string|null, crossorigin?: bool|string|null, type?: string|null, media?: string|null}
 */
class PerformanceLinks extends GroupedTagBuilder
{
    /**
     * @param  array<string, LinkAttributes>  $preloads
     * @param  array<string, LinkAttributes>  $prefetches
     * @param  array<string, LinkAttributes>  $preconnects
     * @param  array<string, array{href: string}>  $dnsPrefetches
     */
    public function __construct(
        protected array $preloads = [],
        protected array $prefetches = [],
        protected array $preconnects = [],
        protected array $dnsPrefetches = [],
    ) {
        //
    }

    public static function key(): string
    {
        return 'performance';
    }

    public static function headArrayKey(): string
    {
        return 'links.performance';
    }

    public static function headArrayDefault(): mixed
    {
        return [
            'preload' => [],
            'prefetch' => [],
            'preconnect' => [],
            'dnsPrefetch' => [],
        ];
    }

    public static function routeAttributeKeys(): array
    {
        return ['preload', 'prefetch', 'preconnect', 'dnsPrefetch'];
    }

    public static function fromRouteAttribute(string $key, mixed $value): ?self
    {
        return match ($key) {
            'preload' => is_string($value) || is_array($value) ? self::fromPreloadAttributes($value) : null,
            'prefetch' => is_string($value) || is_array($value) ? self::fromPrefetchAttributes($value) : null,
            'preconnect' => is_string($value) || is_array($value) ? self::fromPreconnectAttributes($value) : null,
            'dnsPrefetch' => is_string($value) || is_array($value) ? self::fromDnsPrefetchAttributes($value) : null,
            default => null,
        };
    }

    /**
     * @param  string|array<mixed, mixed>  $preloads
     */
    public static function fromPreloadAttributes(string|array $preloads): ?self
    {
        $links = new self;

        foreach (self::repeatableLinkAttributes($preloads, ['href', 'as', 'crossorigin', 'type', 'media']) as $href => $attributes) {
            if (! $links->addPreloadRouteAttribute($href, $attributes)) {
                return null;
            }
        }

        return $links;
    }

    /**
     * @param  string|array<mixed, mixed>  $prefetches
     */
    public static function fromPrefetchAttributes(string|array $prefetches): ?self
    {
        $links = new self;

        foreach (self::repeatableLinkAttributes($prefetches, ['href', 'as']) as $href => $attributes) {
            if (! $links->addPrefetchRouteAttribute($href, $attributes)) {
                return null;
            }
        }

        return $links;
    }

    /**
     * @param  string|array<mixed, mixed>  $preconnects
     */
    public static function fromPreconnectAttributes(string|array $preconnects): ?self
    {
        $links = new self;

        foreach (self::repeatableLinkAttributes($preconnects, ['href', 'crossorigin']) as $href => $attributes) {
            if (! $links->addPreconnectRouteAttribute($href, $attributes)) {
                return null;
            }
        }

        return $links;
    }

    /**
     * @param  string|array<mixed>  $dnsPrefetches
     */
    public static function fromDnsPrefetchAttributes(string|array $dnsPrefetches): ?self
    {
        $links = new self;

        foreach (self::items($dnsPrefetches) as $href) {
            if (! is_string($href)) {
                return null;
            }

            $links->dnsPrefetch($href);
        }

        return $links;
    }

    public function preload(string $href, ?string $as = null, bool|string|null $crossorigin = null, ImageType|string|null $type = null, ?string $media = null): static
    {
        $this->preloads[$href] = array_filter([
            'href' => $href,
            'as' => $as,
            'crossorigin' => $crossorigin,
            'type' => $type instanceof ImageType ? $type->value : $type,
            'media' => $media,
        ], fn ($value) => ! is_null($value));

        return $this;
    }

    public function prefetch(string $href, ?string $as = null): static
    {
        $this->prefetches[$href] = array_filter([
            'href' => $href,
            'as' => $as,
        ], fn ($value) => ! is_null($value));

        return $this;
    }

    public function preconnect(string $href, bool|string|null $crossorigin = null): static
    {
        $this->preconnects[$href] = array_filter([
            'href' => $href,
            'crossorigin' => $crossorigin,
        ], fn ($value) => ! is_null($value));

        return $this;
    }

    public function dnsPrefetch(string $href): static
    {
        $this->dnsPrefetches[$href] = ['href' => $href];

        return $this;
    }

    public function overlayOn(?TagBuilder $base): static
    {
        if (! $base instanceof self) {
            return $this;
        }

        return new static(
            array_replace($base->preloads, $this->preloads),
            array_replace($base->prefetches, $this->prefetches),
            array_replace($base->preconnects, $this->preconnects),
            array_replace($base->dnsPrefetches, $this->dnsPrefetches),
        );
    }

    public function isEmpty(): bool
    {
        return $this->preloads === []
            && $this->prefetches === []
            && $this->preconnects === []
            && $this->dnsPrefetches === [];
    }

    /**
     * @return array{preload: array<int, LinkAttributes>, prefetch: array<int, LinkAttributes>, preconnect: array<int, LinkAttributes>, dnsPrefetch: array<int, array{href: string}>}
     */
    public function toHeadArray(ResolvedHead $head): array
    {
        return $this->headArray();
    }

    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return [
            ...array_map(fn (array $attributes): string => $tags->linkWithAttributes('preload', $attributes, $tags->stableKey('preload', $attributes['href'])), array_values($this->preloads)),
            ...array_map(fn (array $attributes): string => $tags->linkWithAttributes('prefetch', $attributes, $tags->stableKey('prefetch', $attributes['href'])), array_values($this->prefetches)),
            ...array_map(fn (array $attributes): string => $tags->linkWithAttributes('preconnect', $attributes, $tags->stableKey('preconnect', $attributes['href'])), array_values($this->preconnects)),
            ...array_map(fn (array $attributes): string => $tags->linkWithAttributes('dns-prefetch', $attributes, $tags->stableKey('dns-prefetch', $attributes['href'])), array_values($this->dnsPrefetches)),
        ];
    }

    /**
     * @return array{preload: array<int, LinkAttributes>, prefetch: array<int, LinkAttributes>, preconnect: array<int, LinkAttributes>, dnsPrefetch: array<int, array{href: string}>}
     */
    protected function headArray(): array
    {
        return [
            'preload' => array_values($this->preloads),
            'prefetch' => array_values($this->prefetches),
            'preconnect' => array_values($this->preconnects),
            'dnsPrefetch' => array_values($this->dnsPrefetches),
        ];
    }

    protected static function boolOrString(mixed $value): bool|string|null
    {
        return is_bool($value) || is_string($value) ? $value : null;
    }

    private function addPreloadRouteAttribute(mixed $href, mixed $attributes): bool
    {
        if (is_string($attributes)) {
            $this->preload($attributes);

            return true;
        }

        if (! is_array($attributes) || is_null($url = self::routeAttributeHref($href, $attributes))) {
            return false;
        }

        $this->preload(
            $url,
            as: self::string($attributes['as'] ?? null),
            crossorigin: self::boolOrString($attributes['crossorigin'] ?? null),
            type: self::stringOrBackedEnum($attributes['type'] ?? null),
            media: self::string($attributes['media'] ?? null),
        );

        return true;
    }

    private function addPrefetchRouteAttribute(mixed $href, mixed $attributes): bool
    {
        if (is_string($attributes)) {
            $this->prefetch($attributes);

            return true;
        }

        if (! is_array($attributes) || is_null($url = self::routeAttributeHref($href, $attributes))) {
            return false;
        }

        $this->prefetch($url, as: self::string($attributes['as'] ?? null));

        return true;
    }

    private function addPreconnectRouteAttribute(mixed $href, mixed $attributes): bool
    {
        if (is_string($attributes)) {
            $this->preconnect($attributes);

            return true;
        }

        if (! is_array($attributes) || is_null($url = self::routeAttributeHref($href, $attributes))) {
            return false;
        }

        $this->preconnect($url, crossorigin: self::boolOrString($attributes['crossorigin'] ?? null));

        return true;
    }

    /**
     * @param  string|array<mixed, mixed>  $value
     * @param  array<int, string>  $singleAttributeKeys
     * @return array<mixed, mixed>
     */
    private static function repeatableLinkAttributes(string|array $value, array $singleAttributeKeys): array
    {
        if (is_string($value)) {
            return [$value];
        }

        if (array_is_list($value)) {
            return $value;
        }

        foreach ($singleAttributeKeys as $key) {
            if (array_key_exists($key, $value)) {
                return [$value];
            }
        }

        return $value;
    }

    /**
     * @param  array<mixed, mixed>  $attributes
     */
    private static function routeAttributeHref(mixed $href, array $attributes): ?string
    {
        return self::string($attributes['href'] ?? null) ?? self::string($href);
    }
}
