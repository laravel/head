<?php

declare(strict_types=1);

namespace Laravel\Head\Schema;

use DateTimeInterface;
use Laravel\Head\SchemaType;

#[SchemaType('Event')]
class Event extends SchemaObject
{
    public function description(string $description): static
    {
        return $this->set('description', $description);
    }

    public function doorTime(DateTimeInterface|string $doorTime): static
    {
        return $this->date('doorTime', $doorTime);
    }

    public function endDate(DateTimeInterface|string $endDate): static
    {
        return $this->date('endDate', $endDate);
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

    public function organizer(Organization|Person $organizer): static
    {
        return $this->set('organizer', $organizer);
    }

    public function startDate(DateTimeInterface|string $startDate): static
    {
        return $this->date('startDate', $startDate);
    }
}
