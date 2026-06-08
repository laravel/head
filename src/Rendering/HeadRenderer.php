<?php

declare(strict_types=1);

namespace Laravel\Head\Rendering;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Head\HeadData;
use Laravel\Head\Metadata\Metadata;
use Laravel\Head\MetadataRegistry;
use Laravel\Head\Schema\SchemaValidator;

class HeadRenderer
{
    protected TagRenderer $tags;

    public function __construct(
        protected SchemaValidator $schemas,
        protected MetadataRegistry $metadata,
        ?TagRenderer $tags = null,
    ) {
        $this->tags = $tags ?? new TagRenderer;
    }

    public function render(HeadData $head, ?Request $request = null): string
    {
        $resolved = $this->resolve($head, $request);

        return collect($resolved->sections())
            ->flatMap(fn (Metadata $section): array => $section->toTags($resolved, $this->tags))
            ->filter()
            ->implode(PHP_EOL);
    }

    /**
     * Render the resolved head as the array returned by Head::toArray().
     *
     * This same head array is shared as the Inertia "head" prop by the service
     * provider when Inertia is installed.
     *
     * @return array<string, mixed>
     */
    public function toArray(HeadData $head, ?Request $request = null): array
    {
        $resolved = $this->resolve($head, $request);

        $headArray = [];

        foreach ($this->metadata->metadata() as $section) {
            Arr::set($headArray, $section::headArrayKey(), $resolved->headArray($section));
        }

        return $headArray;
    }

    protected function resolve(HeadData $head, ?Request $request): ResolvedHead
    {
        return new ResolvedHead($head, $this->metadata, $request, $this->schemas);
    }
}
