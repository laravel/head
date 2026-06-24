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
    ) {}

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
            'preload' => is_array($value) ? self::fromPreloadAttributes($value) : null,
            'prefetch' => is_array($value) ? self::fromPrefetchAttributes($value) : null,
            'preconnect' => is_array($value) ? self::fromPreconnectAttributes($value) : null,
            'dnsPrefetch' => is_array($value) ? self::fromDnsPrefetchAttributes($value) : null,
            default => null,
        };
    }

    /**
     * @param  array<mixed, mixed>  $preloads
     */
    public static function fromPreloadAttributes(array $preloads): self
    {
        $links = new self;

        foreach ($preloads as $href => $attributes) {
            if (is_string($attributes)) {
                $links->preload($attributes);
            }

            if (is_array($attributes)) {
                $links->preload(
                    self::string($attributes['href'] ?? null) ?? self::string($href) ?? '',
                    as: self::string($attributes['as'] ?? null),
                    crossorigin: self::boolOrString($attributes['crossorigin'] ?? null),
                    type: self::stringOrBackedEnum($attributes['type'] ?? null),
                    media: self::string($attributes['media'] ?? null),
                );
            }
        }

        return $links;
    }

    /**
     * @param  array<mixed, mixed>  $prefetches
     */
    public static function fromPrefetchAttributes(array $prefetches): self
    {
        $links = new self;

        foreach ($prefetches as $href => $attributes) {
            if (is_string($attributes)) {
                $links->prefetch($attributes);
            }

            if (is_array($attributes)) {
                $links->prefetch(
                    self::string($attributes['href'] ?? null) ?? self::string($href) ?? '',
                    as: self::string($attributes['as'] ?? null),
                );
            }
        }

        return $links;
    }

    /**
     * @param  array<mixed, mixed>  $preconnects
     */
    public static function fromPreconnectAttributes(array $preconnects): self
    {
        $links = new self;

        foreach ($preconnects as $href => $attributes) {
            if (is_string($attributes)) {
                $links->preconnect($attributes);
            }

            if (is_array($attributes)) {
                $links->preconnect(
                    self::string($attributes['href'] ?? null) ?? self::string($href) ?? '',
                    crossorigin: self::boolOrString($attributes['crossorigin'] ?? null),
                );
            }
        }

        return $links;
    }

    /**
     * @param  array<mixed>  $dnsPrefetches
     */
    public static function fromDnsPrefetchAttributes(array $dnsPrefetches): self
    {
        $links = new self;

        foreach ($dnsPrefetches as $href) {
            if (is_string($href)) {
                $links->dnsPrefetch($href);
            }
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
}
