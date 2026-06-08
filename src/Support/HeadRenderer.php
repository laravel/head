<?php

declare(strict_types=1);

namespace Laravel\Head\Support;

use Illuminate\Http\Request;
use Laravel\Head\Metadata\AlternateLinks;
use Laravel\Head\Metadata\Canonical;
use Laravel\Head\Metadata\Description;
use Laravel\Head\Metadata\FeedLinks;
use Laravel\Head\Metadata\GenericLinks;
use Laravel\Head\Metadata\Metadata;
use Laravel\Head\Metadata\MetaTags;
use Laravel\Head\Metadata\OpenGraph;
use Laravel\Head\Metadata\PaginationLinks;
use Laravel\Head\Metadata\PerformanceLinks;
use Laravel\Head\Metadata\ResolvedHead;
use Laravel\Head\Metadata\Robots;
use Laravel\Head\Metadata\Schemas;
use Laravel\Head\Metadata\TagRenderer;
use Laravel\Head\Metadata\Title;
use Laravel\Head\Metadata\Twitter;
use Laravel\Head\Schema\SchemaValidator;

class HeadRenderer
{
    protected TagRenderer $tags;

    public function __construct(
        protected SchemaValidator $schemas,
        ?TagRenderer $tags = null,
    ) {
        $this->tags = $tags ?? new TagRenderer;
    }

    public function render(HeadData $head, ?Request $request = null): string
    {
        $resolved = new ResolvedHead($head, $request);

        return collect($this->orderedSections($head))
            ->flatMap(fn (Metadata $section): array => $this->tagsFor($section, $resolved))
            ->filter()
            ->implode(PHP_EOL);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(HeadData $head, ?Request $request = null): array
    {
        $resolved = new ResolvedHead($head, $request);

        return [
            'title' => $this->payload($resolved, Title::class),
            'description' => $this->payload($resolved, Description::class),
            'canonical' => $this->payload($resolved, Canonical::class),
            'robots' => $this->payload($resolved, Robots::class),
            'openGraph' => $this->payload($resolved, OpenGraph::class, []),
            'twitter' => $this->payload($resolved, Twitter::class, []),
            'links' => [
                'performance' => $this->payload($resolved, PerformanceLinks::class, [
                    'preload' => [],
                    'prefetch' => [],
                    'preconnect' => [],
                    'dnsPrefetch' => [],
                ]),
                'pagination' => $this->payload($resolved, PaginationLinks::class, []),
                'alternates' => $this->payload($resolved, AlternateLinks::class, []),
                'feeds' => $this->payload($resolved, FeedLinks::class, []),
                'generic' => $this->payload($resolved, GenericLinks::class, []),
            ],
            'meta' => $this->payload($resolved, MetaTags::class, []),
            'schemas' => $this->schemasPayload($resolved),
        ];
    }

    /**
     * @return array<int, Metadata>
     */
    protected function orderedSections(HeadData $head): array
    {
        $ordered = [];

        $resolved = new ResolvedHead($head);

        foreach (HeadData::metadata() as $section) {
            if ($value = $head->get($section)) {
                $ordered[] = $value;

                continue;
            }

            if ($section === Twitter::class && ($resolved->title() || $resolved->description() || $resolved->openGraphImage())) {
                $ordered[] = new Twitter;
            }
        }

        return $ordered;
    }

    /**
     * @param  class-string<Metadata>  $section
     */
    protected function payload(ResolvedHead $resolved, string $section, mixed $default = null): mixed
    {
        if ($section === Twitter::class) {
            return ($resolved->section(Twitter::class) ?? new Twitter)->toPayload($resolved);
        }

        return $resolved->section($section)?->toPayload($resolved) ?? $default;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function schemasPayload(ResolvedHead $resolved): array
    {
        $section = $resolved->section(Schemas::class);

        if (! $section instanceof Schemas) {
            return [];
        }

        $payload = $section->toPayload($resolved);

        foreach ($payload as $schema) {
            $this->schemas->validate($schema);
        }

        return $payload;
    }

    /**
     * @return array<int, string>
     */
    protected function tagsFor(Metadata $section, ResolvedHead $resolved): array
    {
        if ($section instanceof Schemas) {
            return array_map(
                fn (array $schema): string => $this->tags->jsonLd($schema),
                $this->schemasPayload($resolved),
            );
        }

        return $section->toTags($resolved, $this->tags);
    }
}
