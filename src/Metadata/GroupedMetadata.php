<?php

declare(strict_types=1);

namespace Laravel\Head\Metadata;

abstract class GroupedMetadata extends Metadata
{
    /**
     * Grouped metadata is represented as an empty array when absent from the
     * Head::toArray() / Inertia head array.
     */
    public static function headArrayDefault(): mixed
    {
        return [];
    }

    /**
     * @return array<string, string>
     */
    protected static function stringArray(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_filter($value, fn (mixed $item): bool => is_string($item));
    }
}
