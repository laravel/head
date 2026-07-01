<?php

declare(strict_types=1);

namespace Laravel\Head\Tags;

use Laravel\Head\Rendering\ResolvedHead;
use Laravel\Head\Rendering\TagRenderer;

/**
 * @phpstan-consistent-constructor
 */
class AlternateLinks extends GroupedTagBuilder
{
    /**
     * @param  array<string, string>  $links
     */
    public function __construct(protected array $links = [])
    {
        //
    }

    public static function key(): string
    {
        return 'alternates';
    }

    public static function headArrayKey(): string
    {
        return 'links.alternates';
    }

    public static function fromRouteAttribute(string $key, mixed $value): ?self
    {
        if ($key !== 'alternates' || ! is_array($value)) {
            return null;
        }

        $links = new self;

        foreach ($value as $locale => $href) {
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
     * @return array<string, string>
     */
    public function toHeadArray(ResolvedHead $head): array
    {
        return $this->links;
    }

    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return array_map(
            fn (string $hreflang, string $href): string => $tags->linkWithAttributes('alternate', ['hreflang' => $hreflang, 'href' => $href], 'alternate:'.$hreflang),
            array_keys($this->links),
            $this->links,
        );
    }
}
