<?php

declare(strict_types=1);

namespace Laravel\Head\Enums;

enum RobotsRule: string
{
    case All = 'all';
    case Index = 'index';
    case NoIndex = 'noindex';
    case Follow = 'follow';
    case NoFollow = 'nofollow';
    case None = 'none';
    case NoArchive = 'noarchive';
    case NoSnippet = 'nosnippet';
    case NoImageIndex = 'noimageindex';
    case NoTranslate = 'notranslate';
    case IndexIfEmbedded = 'indexifembedded';
}
