<?php

declare(strict_types=1);

namespace Laravel\Head\Schema;

use Laravel\Head\SchemaType;

#[SchemaType('ImageObject')]
class ImageObject extends SchemaObject
{
    public function url(string $url): static
    {
        return $this->set('url', $url);
    }

    public function width(int|string $width): static
    {
        return $this->set('width', $width);
    }

    public function height(int|string $height): static
    {
        return $this->set('height', $height);
    }
}
