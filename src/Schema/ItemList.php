<?php

declare(strict_types=1);

namespace Laravel\Head\Schema;

use Laravel\Head\Enums\ItemListOrder;
use Laravel\Head\SchemaType;

#[SchemaType('ItemList')]
class ItemList extends SchemaObject
{
    /** @var array<int, array<string, mixed>> */
    protected array $items = [];

    public function name(string $name): static
    {
        return $this->set('name', $name);
    }

    public function url(string $url): static
    {
        return $this->set('url', $url);
    }

    /**
     * Append an item, positioned by the order the items are added.
     *
     * @param  SchemaObject|string  $item  A schema object describing the item, or the item name, which the URL then links to.
     */
    public function item(SchemaObject|string $item, ?string $url = null): static
    {
        $element = ['@type' => 'ListItem', 'position' => count($this->items) + 1];

        if ($item instanceof SchemaObject) {
            $element['item'] = $item->toArray();
        } else {
            $element['name'] = $item;

            if ($url !== null) {
                $element['url'] = $url;
            }
        }

        $this->items[] = $element;

        return $this->set('itemListElement', $this->items);
    }

    /**
     * @param  array<string, string>|array<int, SchemaObject|string>  $items  Item names keyed to their URLs, or a list of names or schema objects.
     */
    public function items(array $items): static
    {
        foreach ($items as $name => $item) {
            if (is_string($name) && is_string($item)) {
                $this->item($name, $item);

                continue;
            }

            $this->item($item);
        }

        return $this;
    }

    public function itemListOrder(ItemListOrder $order): static
    {
        return $this->set('itemListOrder', $order->url());
    }
}
