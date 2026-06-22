<?php

declare(strict_types=1);

namespace Laravel\Head\Schema;

use Laravel\Head\Enums\OfferAvailability;
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

    public function availability(OfferAvailability $availability): static
    {
        return $this->set('availability', $availability->url());
    }
}
