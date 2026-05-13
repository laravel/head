<?php

declare(strict_types=1);

namespace Laravel\Head\Schema;

use Laravel\Head\SchemaType;

#[SchemaType('Offer')]
class Offer extends SchemaObject
{
    public function price(float|int|string $price): static
    {
        return $this->set('price', $price);
    }

    public function priceCurrency(string $currency): static
    {
        return $this->set('priceCurrency', $currency);
    }

    public function availability(string $availability): static
    {
        return $this->set('availability', str_starts_with($availability, 'https://schema.org/')
            ? $availability
            : 'https://schema.org/'.$availability);
    }
}
