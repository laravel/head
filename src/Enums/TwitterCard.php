<?php

declare(strict_types=1);

namespace Laravel\Head\Enums;

enum TwitterCard: string
{
    case Summary = 'summary';
    case SummaryLargeImage = 'summary_large_image';
    case App = 'app';
    case Player = 'player';
}
