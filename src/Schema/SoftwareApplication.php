<?php

declare(strict_types=1);

namespace Laravel\Head\Schema;

use Laravel\Head\SchemaType;

#[SchemaType('SoftwareApplication')]
class SoftwareApplication extends SchemaObject
{
    public function applicationCategory(string $category): static
    {
        return $this->set('applicationCategory', $category);
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

    public function name(string $name): static
    {
        return $this->set('name', $name);
    }

    /**
     * @param  Offer|array<int, Offer>  $offers
     */
    public function offers(Offer|array $offers): static
    {
        return $this->set('offers', $offers);
    }

    public function operatingSystem(string $operatingSystem): static
    {
        return $this->set('operatingSystem', $operatingSystem);
    }

    public function url(string $url): static
    {
        return $this->set('url', $url);
    }
}
