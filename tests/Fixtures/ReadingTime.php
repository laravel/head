<?php

declare(strict_types=1);

namespace Laravel\Head\Tests\Fixtures;

use Laravel\Head\Rendering\ResolvedHead;
use Laravel\Head\Rendering\TagRenderer;
use Laravel\Head\Tags\TagBuilder;

/**
 * @phpstan-consistent-constructor
 */
class ReadingTime extends TagBuilder
{
    public function __construct(protected ?int $minutes = null) {}

    public static function key(): string
    {
        return 'readingTime';
    }

    public static function make(int $minutes): self
    {
        return new self($minutes);
    }

    public static function fromRouteAttribute(string $key, mixed $value): ?self
    {
        return $key === 'readingTime' && is_int($value) ? self::make($value) : null;
    }

    public function toHeadArray(ResolvedHead $head): ?int
    {
        return $this->minutes;
    }

    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return is_null($this->minutes)
            ? []
            : [$tags->meta('name', 'twitter:label1', 'Reading time'), $tags->meta('name', 'twitter:data1', $this->minutes.' min read')];
    }

    public function overlayOn(?TagBuilder $base): static
    {
        if (! $base instanceof static) {
            return $this;
        }

        return new static($this->minutes ?? $base->minutes);
    }

    public function isEmpty(): bool
    {
        return is_null($this->minutes);
    }
}
