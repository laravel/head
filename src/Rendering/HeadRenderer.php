<?php

declare(strict_types=1);

namespace Laravel\Head\Rendering;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Head\HeadData;
use Laravel\Head\Schema\SchemaValidator;
use Laravel\Head\TagRegistry;
use Laravel\Head\Tags\TagBuilder;

class HeadRenderer
{
    protected TagRenderer $tags;

    public function __construct(
        protected SchemaValidator $schemas,
        protected TagRegistry $registry,
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
     * @return array<int, string>
     */
    public function toElements(HeadData $head, ?Request $request = null): array
    {
        return $this->elements($head, $request, $this->tags);
    }

    /**
     * Render the resolved head as individual HTML element strings with stable
     * data-inertia keys for Inertia's server-managed head reconciler.
     *
     * @return array<int, string>
     */
    public function toInertiaElements(HeadData $head, ?Request $request = null): array
    {
        return $this->elements($head, $request, $this->tags->withInertiaAttributes());
    }

    /**
     * @return array<int, string>
     */
    protected function elements(HeadData $head, ?Request $request, TagRenderer $tags): array
    {
        $resolved = new ResolvedHead($head, $this->registry, $request, $this->schemas);

        return collect($resolved->builders())
            ->flatMap(fn (TagBuilder $builder): array => $builder->toTags($resolved, $tags))
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

        foreach ($this->registry->builders() as $builder) {
            Arr::set($headArray, $builder::headArrayKey(), $resolved->headArray($builder));
        }

        return $headArray;
    }
}
