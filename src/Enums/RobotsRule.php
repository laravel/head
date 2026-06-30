<?php

declare(strict_types=1);

namespace Laravel\Head\Enums;

enum RobotsRule: string
{
    /**
     * Shorthand for index, follow
     */
    case All = 'all';

    /**
     * Allow indexing
     */
    case Index = 'index';

    /**
     * Prevent indexing
     */
    case NoIndex = 'noindex';

    /**
     * Allow following links
     */
    case Follow = 'follow';

    /**
     * Prevent following links
     */
    case NoFollow = 'nofollow';

    /**
     * Shorthand for noindex, nofollow
     */
    case None = 'none';

    /**
     * Don't show a cached copy
     */
    case NoArchive = 'noarchive';

    /**
     * Don't show a text snippet or video preview
     */
    case NoSnippet = 'nosnippet';

    /**
     * Don't index images on the page
     */
    case NoImageIndex = 'noimageindex';

    /**
     * Don't offer translation
     */
    case NoTranslate = 'notranslate';

    /**
     * Index the page when it's embedded through iframes
     */
    case IndexIfEmbedded = 'indexifembedded';
}
