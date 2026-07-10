<?php

declare(strict_types=1);

namespace Laravel\Head\Schema;

use Laravel\Head\SchemaType;

#[SchemaType('BreadcrumbList')]
class Breadcrumbs extends SchemaObject
{
    /** @var array<int, array{'@type': string, position: int, name: string, item: string}> */
    protected array $items = [];

    public function item(string $name, string $url): static
    {
        $this->items[] = [
            '@type' => 'ListItem',
            'position' => count($this->items) + 1,
            'name' => $name,
            'item' => $url,
        ];

        return $this->set('itemListElement', $this->items);
    }
}
