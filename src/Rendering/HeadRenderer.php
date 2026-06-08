<?php

declare(strict_types=1);

namespace Laravel\Head\Rendering;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Head\HeadData;
use Laravel\Head\Metadata\Metadata;
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
        $resolved = $this->resolve($head, $request);

        return collect($resolved->sections())
            ->flatMap(fn (Metadata $section): array => $section->toTags($resolved, $this->tags))
            ->filter()
            ->implode(PHP_EOL);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(HeadData $head, ?Request $request = null): array
    {
        $resolved = $this->resolve($head, $request);

        $payload = [];

        foreach (HeadData::metadata() as $section) {
            Arr::set($payload, $section::payloadKey(), $resolved->payload($section));
        }

        return $payload;
    }

    protected function resolve(HeadData $head, ?Request $request): ResolvedHead
    {
        return new ResolvedHead($head, $request, $this->schemas);
    }
}
