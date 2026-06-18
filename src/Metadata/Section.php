<?php

declare(strict_types=1);

namespace Laravel\Head\Metadata;

use Laravel\Head\Rendering\ResolvedHead;
use Laravel\Head\Rendering\TagRenderer;

abstract class Section
{
    abstract public static function key(): string;

    /**
     * Merge this section over the given base section, preferring this section's values.
     */
    abstract public function overlayOn(?self $base): static;

    abstract public function isEmpty(): bool;

    /**
     * The dot-notated key this section occupies in the Head::toArray() result.
     */
    public static function headArrayKey(): string
    {
        return static::key();
    }

    /**
     * The value used in Head::toArray() when this section has no metadata.
     */
    public static function headArrayDefault(): mixed
    {
        return null;
    }

    /**
     * @return array<int, string>
     */
    public static function routeAttributeKeys(): array
    {
        return [static::key()];
    }

    public static function fromRouteAttribute(string $key, mixed $value): ?self
    {
        return null;
    }

    public static function rendersWhenEmpty(ResolvedHead $head): bool
    {
        return false;
    }

    public function asDefaults(): static
    {
        return $this;
    }

    /**
     * Convert this section into its Head::toArray() value.
     */
    public function toHeadArray(ResolvedHead $head): mixed
    {
        return null;
    }

    /**
     * Convert this section into the HTML tags rendered by @head.
     *
     * @return array<int, string>
     */
    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return [];
    }

    protected static function string(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }

    protected static function bool(mixed $value): ?bool
    {
        return is_bool($value) ? $value : null;
    }

    protected static function int(mixed $value): ?int
    {
        return is_int($value) ? $value : null;
    }
}
