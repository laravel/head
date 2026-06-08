<?php

declare(strict_types=1);

namespace Laravel\Head\Metadata;

use Laravel\Head\Rendering\ResolvedHead;
use Laravel\Head\Rendering\TagRenderer;

/**
 * @phpstan-consistent-constructor
 *
 * @phpstan-type MetaAttributes array{key: string, content: string, property?: bool|null}
 */
class MetaTags extends GroupedMetadata
{
    /**
     * @param  array<string, MetaAttributes>  $tags
     */
    public function __construct(protected array $tags = []) {}

    public static function key(): string
    {
        return 'meta';
    }

    public static function fromAttributeValue(string $key, mixed $value): ?self
    {
        return $key === 'meta' && is_array($value) ? self::fromAttributes($value) : null;
    }

    /**
     * @param  array<mixed, mixed>  $values
     */
    public static function fromAttributes(array $values): self
    {
        $tags = new self;

        foreach ($values as $key => $value) {
            if (is_string($key) && is_string($value)) {
                $tags->tag($key, $value);
            }

            if (is_array($value) && is_string($value['key'] ?? null) && is_string($value['content'] ?? null)) {
                $tags->tag($value['key'], $value['content'], self::bool($value['property'] ?? null));
            }
        }

        return $tags;
    }

    public function tag(string $key, string $content, ?bool $property = null): static
    {
        $this->tags[$key] = array_filter([
            'key' => $key,
            'content' => $content,
            'property' => $property,
        ], fn (mixed $value): bool => ! is_null($value));

        return $this;
    }

    public function overlay(?Metadata $base): static
    {
        if (! $base instanceof self) {
            return $this;
        }

        return new static(array_replace($base->tags, $this->tags));
    }

    /**
     * @return array<int, MetaAttributes>
     */
    public function headArray(): array
    {
        return array_values($this->tags);
    }

    /**
     * @return array<int, MetaAttributes>
     */
    public function toHeadArray(ResolvedHead $head): array
    {
        return $this->headArray();
    }

    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return array_map(function (array $meta) use ($tags): string {
            $attribute = ($meta['property'] ?? $this->isRdfaProperty($meta['key'])) ? 'property' : 'name';

            return $tags->meta($attribute, $meta['key'], $meta['content']);
        }, $this->headArray());
    }

    protected function isRdfaProperty(string $key): bool
    {
        foreach (['og:', 'article:', 'book:', 'profile:', 'music:', 'video:', 'fb:', 'product:'] as $prefix) {
            if (str_starts_with($key, $prefix)) {
                return true;
            }
        }

        return false;
    }

    public function isEmpty(): bool
    {
        return $this->tags === [];
    }

    /**
     * @return array{tags: array<string, MetaAttributes>}
     */
    protected function parts(): array
    {
        return ['tags' => $this->tags];
    }

    /**
     * @param  array<string, mixed>  $parts
     */
    protected function withParts(array $parts): static
    {
        return new static(self::tagsArray($parts['tags'] ?? null));
    }

    /**
     * @return array<string, MetaAttributes>
     */
    protected static function tagsArray(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $tags = [];

        foreach ($value as $key => $tag) {
            if (is_string($key) && is_array($tag) && is_string($tag['key'] ?? null) && is_string($tag['content'] ?? null)) {
                $tags[$key] = array_filter([
                    'key' => $tag['key'],
                    'content' => $tag['content'],
                    'property' => self::bool($tag['property'] ?? null),
                ], fn (mixed $item): bool => ! is_null($item));
            }
        }

        return $tags;
    }
}
