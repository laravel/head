<?php

declare(strict_types=1);

namespace Laravel\Head\Schema;

use Laravel\Head\SchemaType;

#[SchemaType('Place')]
class Place extends SchemaObject
{
    public function name(string $name): static
    {
        return $this->set('name', $name);
    }

    /**
     * @param  SchemaObject|array<string, mixed>|string  $address
     */
    public function address(SchemaObject|array|string $address): static
    {
        return $this->set('address', $address);
    }

    public function url(string $url): static
    {
        return $this->set('url', $url);
    }
}
