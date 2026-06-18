<?php

declare(strict_types=1);

namespace Laravel\Head\Metadata;

use Laravel\Head\Rendering\ResolvedHead;
use Laravel\Head\Rendering\TagRenderer;

class Robots extends StringSection
{
    public static function key(): string
    {
        return 'robots';
    }

    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return ($robots = $this->render()) ? [$tags->meta('name', 'robots', $robots, 'robots')] : [];
    }
}
