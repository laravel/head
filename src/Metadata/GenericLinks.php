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
class GenericLinks extends Metadata
{
    /**
     * @param  array<string, LinkAttributes>  $links
     */
    public function __construct(protected array $links = []) {}

    public static function key(): string
    {
        return 'links';
    }

    public static function payloadKey(): string
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

    public function overlay(?Metadata $base): static
    {
        if (! $base instanceof self) {
            return $this;
        }

        return new static(array_replace($base->links, $this->links));
    }

    /**
     * @return array<int, LinkAttributes>
     */
    public function payload(): array
    {
        return array_values($this->links);
    }

    /**
     * @return array<int, LinkAttributes>
     */
    public function toPayload(ResolvedHead $head): array
    {
        return $this->payload();
    }

    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return array_map(function (array $link) use ($tags): string {
            return $tags->linkWithAttributes($link['rel'], ['href' => $link['href'], ...$link['attributes']]);
        }, $this->payload());
    }

    public function isEmpty(): bool
    {
        return $this->links === [];
    }

    /**
     * @return array{links: array<string, LinkAttributes>}
     */
    protected function parts(): array
    {
        return ['links' => $this->links];
    }

    /**
     * @param  array<string, mixed>  $parts
     */
    protected function withParts(array $parts): static
    {
        return new static(self::linksArray($parts['links'] ?? null));
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
            fn (mixed $value): bool => is_null($value) || is_bool($value) || is_float($value) || is_int($value) || is_string($value)
        );
    }

    /**
     * @return array<string, LinkAttributes>
     */
    protected static function linksArray(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $links = [];

        foreach ($value as $key => $link) {
            if (! is_string($key) || ! is_array($link) || ! is_string($link['rel'] ?? null) || ! is_string($link['href'] ?? null) || ! is_array($link['attributes'] ?? null)) {
                continue;
            }

            $links[$key] = [
                'rel' => $link['rel'],
                'href' => $link['href'],
                'attributes' => self::attributes(self::named($link['attributes'])),
            ];
        }

        return $links;
    }
}
