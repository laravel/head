<?php

declare(strict_types=1);

namespace Laravel\Head\Tags;

use BackedEnum;
use Laravel\Head\Rendering\ResolvedHead;
use Laravel\Head\Rendering\TagRenderer;

/**
 * @phpstan-consistent-constructor
 *
 * @phpstan-type LinkAttributes array{rel: string, href: string, attributes: array<string, bool|float|int|string|null>}
 */
class GenericLinks extends GroupedTagBuilder
{
    /**
     * @param  array<string, LinkAttributes>  $links
     */
    public function __construct(protected array $links = [])
    {
        //
    }

    public static function key(): string
    {
        return 'links';
    }

    public static function headArrayKey(): string
    {
        return 'links.generic';
    }

    public static function routeAttributeKeys(): array
    {
        return ['link', 'icon', 'favicon', 'appleTouchIcon', 'maskIcon', 'manifest', 'appleTouchStartupImage'];
    }

    public static function fromRouteAttribute(string $key, mixed $value): ?self
    {
        if ($key !== 'link') {
            return self::fromAliasRouteAttribute($key, $value);
        }

        if (! is_array($value)) {
            return null;
        }

        $links = new self;

        foreach (self::items($value) as $link) {
            $links->addRouteAttributeLink($link);
        }

        return $links;
    }

    protected static function fromAliasRouteAttribute(string $key, mixed $value): ?self
    {
        if (! in_array($key, ['icon', 'favicon', 'appleTouchIcon', 'maskIcon', 'manifest', 'appleTouchStartupImage'], true)) {
            return null;
        }

        $links = new self;

        foreach (self::items($value) as $link) {
            $links->addAliasRouteAttributeLink($key, $link);
        }

        return $links;
    }

    /**
     * @param  array<string, BackedEnum|bool|float|int|string|null>  $attributes
     */
    public function link(string $rel, string $href, array $attributes = []): static
    {
        unset($attributes['rel'], $attributes['href']);
        $attributes = self::attributes($attributes);

        $this->links[$rel.' '.$href] = [
            'rel' => $rel,
            'href' => $href,
            'attributes' => $attributes,
        ];

        return $this;
    }

    public function overlayOn(?TagBuilder $base): static
    {
        if (! $base instanceof self) {
            return $this;
        }

        return new static(array_replace($base->links, $this->links));
    }

    public function isEmpty(): bool
    {
        return $this->links === [];
    }

    /**
     * @return array<int, LinkAttributes>
     */
    public function toHeadArray(ResolvedHead $head): array
    {
        return $this->headArray();
    }

    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return array_map(function (array $link) use ($tags): string {
            return $tags->linkWithAttributes($link['rel'], ['href' => $link['href'], ...$link['attributes']], $tags->stableKey('link:'.$link['rel'], $link['href']));
        }, $this->headArray());
    }

    /**
     * @return array<int, LinkAttributes>
     */
    protected function headArray(): array
    {
        return array_values($this->links);
    }

    private function addRouteAttributeLink(mixed $link): void
    {
        if (! is_array($link) || ! is_string($link['rel'] ?? null) || ! is_string($link['href'] ?? null)) {
            return;
        }

        $attributes = self::named($link);
        unset($attributes['rel'], $attributes['href']);

        $this->link($link['rel'], $link['href'], self::attributes($attributes));
    }

    private function addAliasRouteAttributeLink(string $key, mixed $link): void
    {
        $attributes = is_array($link) ? self::named($link) : [];
        $href = self::routeAttributeHref($link, $attributes, $key === 'manifest' ? '/site.webmanifest' : null);

        if (is_null($href)) {
            return;
        }

        match ($key) {
            'icon', 'favicon' => $this->link('icon', $href, self::attributes([
                'type' => self::stringOrBackedEnum($attributes['type'] ?? null),
                'sizes' => $attributes['sizes'] ?? null,
                'media' => $attributes['media'] ?? null,
            ])),
            'appleTouchIcon' => $this->link('apple-touch-icon', $href, self::attributes([
                'sizes' => $attributes['sizes'] ?? null,
            ])),
            'maskIcon' => $this->link('mask-icon', $href, self::attributes([
                'color' => $attributes['color'] ?? null,
            ])),
            'manifest' => $this->link('manifest', $href, self::attributes([
                'crossorigin' => $attributes['crossorigin'] ?? null,
            ])),
            'appleTouchStartupImage' => $this->link('apple-touch-startup-image', $href, self::attributes([
                'media' => $attributes['media'] ?? null,
            ])),
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private static function routeAttributeHref(mixed $value, array $attributes, ?string $default = null): ?string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_string($attributes['href'] ?? null)) {
            return $attributes['href'];
        }

        if ($value === true || is_array($value)) {
            return $default;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, bool|float|int|string|null>
     */
    protected static function attributes(array $values): array
    {
        $attributes = [];

        foreach ($values as $key => $value) {
            if ($value instanceof BackedEnum) {
                $value = $value->value;
            }

            if (is_null($value) || is_bool($value) || is_float($value) || is_int($value) || is_string($value)) {
                $attributes[$key] = $value;
            }
        }

        return $attributes;
    }
}
