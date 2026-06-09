<?php

declare(strict_types=1);

namespace Laravel\Head\Metadata;

abstract class GroupedSection extends Section
{
    /**
     * Grouped metadata sections are represented as an empty array when absent
     * from the Head::toArray() / Inertia head array.
     */
    public static function headArrayDefault(): mixed
    {
        return [];
    }
}
