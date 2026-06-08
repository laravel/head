<?php

declare(strict_types=1);

namespace Laravel\Head\Metadata;

use Laravel\Head\Rendering\ResolvedHead;
use Laravel\Head\Rendering\TagRenderer;

class Description extends StringMetadata
{
    public static function key(): string
    {
        return 'description';
    }

    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return ($description = $this->render()) ? [$tags->meta('name', 'description', $description)] : [];
    }
}
