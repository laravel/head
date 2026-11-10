<?php

declare(strict_types=1);

namespace Laravel\Head\Enums;

enum EventAttendanceMode: string
{
    case Mixed = 'MixedEventAttendanceMode';
    case Offline = 'OfflineEventAttendanceMode';
    case Online = 'OnlineEventAttendanceMode';

    public function url(): string
    {
        return 'https://schema.org/'.$this->value;
    }
}
