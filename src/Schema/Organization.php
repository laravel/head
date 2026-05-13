<?php

declare(strict_types=1);

namespace Laravel\Head\Schema;

use Laravel\Head\SchemaType;

#[SchemaType('Organization')]
class Organization extends SchemaObject
{
    public function name(string $name): static
    {
        return $this->set('name', $name);
    }

    public function url(string $url): static
    {
        return $this->set('url', $url);
    }

    public function logo(string $url): static
    {
        return $this->set('logo', $url);
    }
}
