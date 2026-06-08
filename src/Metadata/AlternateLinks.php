<?php

declare(strict_types=1);

namespace Laravel\Head\Metadata;

/**
 * @phpstan-consistent-constructor
 */
class AlternateLinks extends Metadata
{
    /**
     * @param  array<string, string>  $links
     */
    public function __construct(protected array $links = []) {}

    public static function key(): string
    {
        return 'alternates';
    }

    public static function fromAttributeValue(string $key, mixed $value): ?self
    {
        return $key === 'alternates' && is_array($value) ? self::fromAttributes($value) : null;
    }

    /**
     * @param  array<mixed, mixed>  $alternates
     */
    public static function fromAttributes(array $alternates): self
    {
        $links = new self;

        foreach ($alternates as $locale => $href) {
            if (is_string($href)) {
                $links->link((string) $locale, $href);
            }
        }

        return $links;
    }

    public function link(string $locale, string $href): static
    {
        $this->links[$locale] = $href;

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
     * @return array<string, string>
     */
    public function payload(): array
    {
        return $this->links;
    }

    /**
     * @return array<string, string>
     */
    public function toPayload(ResolvedHead $head): array
    {
        return $this->payload();
    }

    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return array_map(
            fn (string $hreflang, string $href): string => '<link rel="alternate" hreflang="'.e($hreflang).'" href="'.e($href).'">',
            array_keys($this->links),
            $this->links,
        );
    }

    public function isEmpty(): bool
    {
        return $this->links === [];
    }

    /**
     * @return array{links: array<string, string>}
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
        return new static(self::stringArray($parts['links'] ?? null));
    }

    /**
     * @return array<string, string>
     */
    protected static function stringArray(mixed $value): array
    {
        return is_array($value) ? array_filter($value, fn (mixed $item): bool => is_string($item)) : [];
    }
}
