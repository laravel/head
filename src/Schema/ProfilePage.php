<?php

declare(strict_types=1);

namespace Laravel\Head\Schema;

use DateTimeInterface;
use Laravel\Head\SchemaType;

#[SchemaType('ProfilePage')]
class ProfilePage extends WebPage
{
    /**
     * Set the person or organization the profile belongs to.
     */
    public function mainEntity(Person|Organization $entity): static
    {
        return $this->set('mainEntity', $entity);
    }

    public function dateCreated(DateTimeInterface|string $date): static
    {
        return $this->date('dateCreated', $date);
    }

    public function dateModified(DateTimeInterface|string $date): static
    {
        return $this->date('dateModified', $date);
    }
}
