<?php

declare(strict_types=1);

namespace Laravel\Head\Schema;

use Laravel\Head\SchemaType;

#[SchemaType('Product')]
class Product extends SchemaObject
{
    public function name(string $name): static
    {
        return $this->set('name', $name);
    }

    public function description(string $description): static
    {
        return $this->set('description', $description);
    }

    /**
     * @param  string|array<int, string>  $image
     */
    public function image(string|array $image): static
    {
        return $this->set('image', $image);
    }

    public function sku(string $sku): static
    {
        return $this->set('sku', $sku);
    }

    public function brand(SchemaObject|string $brand): static
    {
        return $this->set('brand', $brand);
    }

    /**
     * @param  SchemaObject|array<int, SchemaObject>  $offers
     */
    public function offers(SchemaObject|array $offers): static
    {
        return $this->set('offers', $offers);
    }
}
