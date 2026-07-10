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

    /**
     * Render the resolved head as an HTML string.
     */
    public function render(HeadData $head, ?Request $request = null): string
    {
        return implode(PHP_EOL, $this->toElements($head, $request));
    }

    /**
     * Render the initial Inertia document head as an HTML string.
     */
    public function renderInertiaDocument(HeadData $inertiaGlobals, HeadData $head, ?Request $request = null): string
    {
        return implode(PHP_EOL, $this->toInertiaDocumentElements($inertiaGlobals, $head, $request));
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
     * Render the initial Inertia document head. Inertia globals are emitted as
     * plain, server-owned tags while page tags receive Inertia ownership keys.
     *
     * @return array<int, string>
     */
    public function toInertiaDocumentElements(HeadData $inertiaGlobals, HeadData $head, ?Request $request = null): array
    {
        $globalHead = new ResolvedHead($inertiaGlobals, $this->registry, $request, $this->schemas);
        $pageHead = new ResolvedHead($head, $this->registry, $request, $this->schemas);
        $pageTags = $this->tags->withInertiaAttributes();
        $elements = [];

        foreach ($this->registry->builders() as $builder) {
            if ($globalBuilder = $this->builder($globalHead, $builder)) {
                array_push($elements, ...$globalBuilder->toTags($globalHead, $this->tags));
            }

            if ($pageBuilder = $this->builder($pageHead, $builder)) {
                array_push($elements, ...$pageBuilder->toTags($pageHead, $pageTags));
            }
        }

        return array_values(array_filter($elements));
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
     * Resolve a builder instance, materializing builders that render when empty.
     *
     * @param  class-string<TagBuilder>  $builder
     */
    protected function builder(ResolvedHead $head, string $builder): ?TagBuilder
    {
        $value = $head->builder($builder);

        if (! $value instanceof TagBuilder && $builder::rendersWhenEmpty($head)) {
            $value = new $builder;
        }

        return $value instanceof TagBuilder ? $value : null;
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
