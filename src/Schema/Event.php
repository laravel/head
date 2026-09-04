<?php

declare(strict_types=1);

namespace Laravel\Head\Schema;

use DateTimeInterface;
use Laravel\Head\Enums\EventAttendanceMode;
use Laravel\Head\Enums\EventStatus;
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

    public function eventAttendanceMode(EventAttendanceMode $mode): static
    {
        return $this->set('eventAttendanceMode', $mode->url());
    }

    public function eventStatus(EventStatus $status): static
    {
        return $this->set('eventStatus', $status->url());
    }

    /**
     * @param  string|array<int, string>  $image
     */
    public function image(string|array $image): static
    {
        return $this->set('image', $image);
    }

    /**
     * Set the event location. A string is treated as the name of a physical place.
     */
    public function location(SchemaObject|string $location): static
    {
        return $this->set('location', is_string($location) ? (new Place)->name($location) : $location);
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

    /**
     * @param  Organization|Person|array<int, Organization|Person>  $performer
     */
    public function performer(Organization|Person|array $performer): static
    {
        return $this->set('performer', $performer);
    }

    public function startDate(DateTimeInterface|string $startDate): static
    {
        return $this->date('startDate', $startDate);
    }
}
