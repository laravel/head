<?php

declare(strict_types=1);

namespace Laravel\Head\Tests\Fixtures;

use Laravel\Head\Metadata\Metadata;
use Laravel\Head\Rendering\ResolvedHead;
use Laravel\Head\Rendering\TagRenderer;

/**
 * @phpstan-consistent-constructor
 */
class ReadingTime extends Metadata
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

    public static function fromAttributeValue(string $key, mixed $value): ?self
    {
        return $key === 'readingTime' && is_int($value) ? self::make($value) : null;
    }

    public function toPayload(ResolvedHead $head): ?int
    {
        return $this->minutes;
    }

    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return is_null($this->minutes)
            ? []
            : [$tags->meta('name', 'twitter:label1', 'Reading time'), $tags->meta('name', 'twitter:data1', $this->minutes.' min read')];
    }

    public function isEmpty(): bool
    {
        return is_null($this->minutes);
    }

    /**
     * @return array{minutes: int|null}
     */
    protected function parts(): array
    {
        return ['minutes' => $this->minutes];
    }

    /**
     * @param  array<string, mixed>  $parts
     */
    protected function withParts(array $parts): static
    {
        $minutes = $parts['minutes'] ?? null;

        return new static(is_int($minutes) ? $minutes : null);
    }
}
