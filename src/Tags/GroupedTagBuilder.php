<?php

declare(strict_types=1);

namespace Laravel\Head\Tags;

abstract class GroupedTagBuilder extends TagBuilder
{
    /**
     * Grouped tag builders are represented as an empty array when absent
     * from the Head::toArray() result.
     */
    public static function headArrayDefault(): mixed
    {
        return [];
    }
}
