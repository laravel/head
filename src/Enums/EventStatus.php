<?php

declare(strict_types=1);

namespace Laravel\Head\Enums;

enum EventStatus: string
{
    case Cancelled = 'EventCancelled';
    case MovedOnline = 'EventMovedOnline';
    case Postponed = 'EventPostponed';
    case Rescheduled = 'EventRescheduled';
    case Scheduled = 'EventScheduled';

    public function url(): string
    {
        return 'https://schema.org/'.$this->value;
    }
}
