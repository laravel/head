<?php

declare(strict_types=1);

namespace Laravel\Head\Enums;

enum ItemListOrder: string
{
    case Ascending = 'ItemListOrderAscending';
    case Descending = 'ItemListOrderDescending';
    case Unordered = 'ItemListUnordered';

    public function url(): string
    {
        return 'https://schema.org/'.$this->value;
    }
}
