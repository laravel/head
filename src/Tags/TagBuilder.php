<?php

declare(strict_types=1);

namespace Laravel\Head\Tags;

use BackedEnum;
use Laravel\Head\Rendering\ResolvedHead;
use Laravel\Head\Rendering\TagRenderer;

abstract class TagBuilder
{
    /**
     * The unique name identifying this builder within the head.
     */
    abstract public static function key(): string;

    /**
     * Merge this builder over the given base builder, preferring this builder's values.
     */
    abstract public function overlayOn(?self $base): static;

    abstract public function isEmpty(): bool;

    /**
     * The dot-notated key this builder occupies in the Head::toArray() result.
     */
    public static function headArrayKey(): string
    {
        return static::key();
    }

    /**
     * The value used in Head::toArray() when this builder has no data.
     */
    public static function headArrayDefault(): mixed
    {
        return null;
    }

    /**
     * The route attribute keys this builder accepts values for.
     *
     * @return array<int, string>
     */
    public static function routeAttributeKeys(): array
    {
        return [static::key()];
    }

    /**
     * Create a builder from a route attribute value, or null when the value is invalid.
     */
    public static function fromRouteAttribute(string $key, mixed $value): ?self
    {
        return null;
    }

    /**
     * Determine if the builder should render even when no data was set.
     */
    public static function rendersWhenEmpty(ResolvedHead $head): bool
    {
        return false;
    }

    /**
     * Mark the builder's data as belonging to the defaults layer.
     */
    public function asDefaults(): static
    {
        return $this;
    }

    /**
     * Convert this builder into its Head::toArray() value.
     */
    public function toHeadArray(ResolvedHead $head): mixed
    {
        return null;
    }

    /**
     * Convert this builder into the HTML tags rendered by @head.
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

    protected static function stringOrBackedEnum(mixed $value): ?string
    {
        if (is_string($value)) {
            return $value;
        }

        if ($value instanceof BackedEnum && is_string($value->value)) {
            return $value->value;
        }

        return null;
    }

    protected static function bool(mixed $value): ?bool
    {
        return is_bool($value) ? $value : null;
    }

    protected static function int(mixed $value): ?int
    {
        return is_int($value) ? $value : null;
    }

    /**
     * Normalize a route attribute value into a list of items.
     *
     * @return array<int, mixed>
     */
    protected static function items(mixed $value): array
    {
        return is_array($value) && array_is_list($value) ? $value : [$value];
    }

    /**
     * Filter an array down to its string-keyed values.
     *
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
}
