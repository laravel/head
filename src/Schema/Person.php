<?php

declare(strict_types=1);

namespace Laravel\Head\Schema;

use Laravel\Head\SchemaType;

#[SchemaType('Person')]
class Person extends SchemaObject
{
    public function name(string $name): static
    {
        return $this->set('name', $name);
    }

    public function url(string $url): static
    {
        return $this->set('url', $url);
    }

    /**
     * @param  string|array<int, string>  $image
     */
    public function image(string|array $image): static
    {
        return $this->set('image', $image);
    }

    /**
     * @param  string  ...$urls  Authoritative pages for the same person, such as a social profile or a Wikipedia entry.
     */
    public function sameAs(string ...$urls): static
    {
        return $this->set('sameAs', count($urls) === 1 ? $urls[0] : $urls);
    }

    /**
     * @param  string|SchemaObject  $identifier  An identifier value, or a PropertyValue schema object naming its scheme.
     */
    public function identifier(string|SchemaObject $identifier): static
    {
        return $this->set('identifier', $identifier);
    }
}
