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

    /**
     * @param  string|SchemaObject  $logo  The logo URL, or an ImageObject schema object carrying its dimensions.
     */
    public function logo(string|SchemaObject $logo): static
    {
        return $this->set('logo', $logo);
    }

    /**
     * @param  string  ...$urls  Authoritative pages for the same organization, such as a social profile or a Wikipedia entry.
     */
    public function sameAs(string ...$urls): static
    {
        return $this->set('sameAs', count($urls) === 1 ? $urls[0] : $urls);
    }
}
