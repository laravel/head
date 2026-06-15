<?php

declare(strict_types=1);

namespace Laravel\Head\Rendering;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Head\HeadData;
use Laravel\Head\Metadata\Section;
use Laravel\Head\MetadataRegistry;
use Laravel\Head\Schema\SchemaValidator;

class HeadRenderer
{
    protected TagRenderer $tags;

    public function __construct(
        protected SchemaValidator $schemas,
        protected MetadataRegistry $registry,
        ?TagRenderer $tags = null,
    ) {
        $this->tags = $tags ?? new TagRenderer;
    }

    public function render(HeadData $head, ?Request $request = null): string
    {
        return implode(PHP_EOL, $this->toElements($head, $request));
    }

    /**
     * Render the resolved head as individual HTML element strings.
     *
     * These elements are shared with Inertia as the page's head prop by the
     * service provider when Inertia is installed.
     *
     * @return array<int, string>
     */
    public function toElements(HeadData $head, ?Request $request = null): array
    {
        $resolved = new ResolvedHead($head, $this->registry, $request, $this->schemas);

        return collect($resolved->sections())
            ->flatMap(fn (Section $section): array => $section->toTags($resolved, $this->tags))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Render the resolved head as the array returned by Head::toArray().
     *
     * @return array<string, mixed>
     */
    public function toArray(HeadData $head, ?Request $request = null): array
    {
        $resolved = new ResolvedHead($head, $this->registry, $request, $this->schemas);

        $headArray = [];

        foreach ($this->registry->sections() as $section) {
            Arr::set($headArray, $section::headArrayKey(), $resolved->headArray($section));
        }

        return $headArray;
    }
}
