<?php

declare(strict_types=1);

namespace Laravel\Head\Schema;

use Laravel\Head\SchemaType;

#[SchemaType('WebPage')]
class WebPage extends SchemaObject
{
    public function name(string $name): static
    {
        return $this->set('name', $name);
    }

    public function description(string $description): static
    {
        return $this->set('description', $description);
    }

    public function url(string $url): static
    {
        return $this->set('url', $url);
    }
}
