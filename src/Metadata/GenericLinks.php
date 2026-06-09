<?php

declare(strict_types=1);

namespace Laravel\Head\Metadata;

use Laravel\Head\Rendering\ResolvedHead;
use Laravel\Head\Rendering\TagRenderer;

/**
 * @phpstan-consistent-constructor
 *
 * @phpstan-type LinkAttributes array{rel: string, href: string, attributes: array<string, bool|float|int|string|null>}
 */
class GenericLinks extends GroupedSection
{
    /**
     * @param  array<string, LinkAttributes>  $links
     */
    public function __construct(protected array $links = []) {}

    public static function key(): string
    {
        return 'links';
    }

    public static function headArrayKey(): string
    {
        return 'links.generic';
    }

    public static function attributeKeys(): array
    {
        return ['link'];
    }

    public static function fromAttributeValue(string $key, mixed $value): ?self
    {
        return $key === 'link' && is_array($value) ? self::fromAttributes($value) : null;
    }

    /**
     * @param  array<mixed, mixed>  $values
     */
    public static function fromAttributes(array $values): self
    {
        $links = new self;

        foreach (self::items($values) as $value) {
            if (! is_array($value) || ! is_string($value['rel'] ?? null) || ! is_string($value['href'] ?? null)) {
                continue;
            }

            $attributes = self::named($value);
            unset($attributes['rel'], $attributes['href']);

            $links->link($value['rel'], $value['href'], self::attributes($attributes));
        }

        return $links;
    }

    /**
     * @param  array<string, bool|float|int|string|null>  $attributes
     */
    public function link(string $rel, string $href, array $attributes = []): static
    {
        unset($attributes['rel'], $attributes['href']);

        $this->links[$rel.' '.$href] = [
            'rel' => $rel,
            'href' => $href,
            'attributes' => $attributes,
        ];

        return $this;
    }

    public function overlayOn(?Section $base): static
    {
        if (! $base instanceof self) {
            return $this;
        }

        return new static(array_replace($base->links, $this->links));
    }

    /**
     * @return array<int, LinkAttributes>
     */
    protected function headArray(): array
    {
        return array_values($this->links);
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
            return $tags->linkWithAttributes($link['rel'], ['href' => $link['href'], ...$link['attributes']]);
        }, $this->headArray());
    }

    public function isEmpty(): bool
    {
        return $this->links === [];
    }

    /**
     * @return array<int, mixed>
     */
    protected static function items(mixed $value): array
    {
        return is_array($value) && array_is_list($value) ? $value : [$value];
    }

    /**
     * @param  array<mixed, mixed>  $values
     * @return array<string, mixed>
     */
    protected static function named(array $values): array
    {
        $named = [];

        foreach ($values as $key => $value) {
            if (is_string($key)) {
                $named[$key] = $value;
            }
        }

        return $named;
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, bool|float|int|string|null>
     */
    protected static function attributes(array $values): array
    {
        return array_filter(
            $values,
            fn ($value) => is_null($value) || is_bool($value) || is_float($value) || is_int($value) || is_string($value)
        );
    }
}
