<?php

declare(strict_types=1);

namespace Laravel\Head\Metadata;

use Laravel\Head\Rendering\ResolvedHead;
use Laravel\Head\Rendering\TagRenderer;

/**
 * @phpstan-consistent-constructor
 */
class AlternateLinks extends GroupedSection
{
    /**
     * @param  array<string, string>  $links
     */
    public function __construct(protected array $links = []) {}

    public static function key(): string
    {
        return 'alternates';
    }

    public static function headArrayKey(): string
    {
        return 'links.alternates';
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

    public function overlayOn(?Section $base): static
    {
        if (! $base instanceof self) {
            return $this;
        }

        return new static(array_replace($base->links, $this->links));
    }

    /**
     * @return array<string, string>
     */
    protected function headArray(): array
    {
        return $this->links;
    }

    /**
     * @return array<string, string>
     */
    public function toHeadArray(ResolvedHead $head): array
    {
        return $this->headArray();
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
}
