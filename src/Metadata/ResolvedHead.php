<?php

declare(strict_types=1);

namespace Laravel\Head\Metadata;

use Illuminate\Http\Request;
use Laravel\Head\Support\HeadData;

/**
 * @phpstan-import-type MediaAttributes from OpenGraph
 */
class ResolvedHead
{
    public function __construct(
        protected HeadData $data,
        protected ?Request $request = null,
    ) {}

    public function data(): HeadData
    {
        return $this->data;
    }

    public function request(): ?Request
    {
        return $this->request;
    }

    public function title(): ?string
    {
        $title = $this->section(Title::class);

        return $title instanceof Title ? $title->render() : null;
    }

    public function description(): ?string
    {
        $description = $this->section(Description::class);

        return $description instanceof Description ? $description->render() : null;
    }

    public function canonical(): ?string
    {
        $canonical = $this->section(Canonical::class);

        return $canonical instanceof Canonical ? $canonical->render($this->request) : null;
    }

    public function robots(): ?string
    {
        $robots = $this->section(Robots::class);

        return $robots instanceof Robots ? $robots->render() : null;
    }

    /**
     * @return array{url: string, alt?: string|null}|null
     */
    public function openGraphImage(): ?array
    {
        $openGraph = $this->section(OpenGraph::class);

        if (! $openGraph instanceof OpenGraph || ($images = $openGraph->images()) === []) {
            return null;
        }

        $image = reset($images);

        return array_filter([
            'url' => $image['url'],
            'alt' => $image['alt'] ?? null,
        ], fn (mixed $value): bool => ! is_null($value));
    }

    /**
     * @param  class-string<Metadata>  $section
     */
    public function section(string $section): ?Metadata
    {
        return $this->data->get($section);
    }
}
