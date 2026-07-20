<?php

declare(strict_types=1);

namespace Laravel\Head\Enums;

enum TwitterCard: string
{
    case Summary = 'summary';
    case SummaryWithLargeImage = 'summary_large_image';
    case App = 'app';
    case Player = 'player';
}
