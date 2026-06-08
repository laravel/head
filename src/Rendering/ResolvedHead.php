<?php

declare(strict_types=1);

namespace Laravel\Head\Rendering;

use Illuminate\Http\Request;
use Laravel\Head\HeadData;
use Laravel\Head\Metadata\Canonical;
use Laravel\Head\Metadata\Description;
use Laravel\Head\Metadata\Metadata;
use Laravel\Head\Metadata\OpenGraph;
use Laravel\Head\Metadata\Robots;
use Laravel\Head\Metadata\Title;
use Laravel\Head\Schema\SchemaValidator;

/**
 * @phpstan-import-type MediaAttributes from \Laravel\Head\Metadata\OpenGraph
 */
class ResolvedHead
{
    public function __construct(
        protected HeadData $data,
        protected ?Request $request = null,
        protected ?SchemaValidator $schemas = null,
    ) {}

    public function data(): HeadData
    {
        return $this->data;
    }

    public function request(): ?Request
    {
        return $this->request;
    }

    /**
     * @param  array<string, mixed>  $schema
     */
    public function validateSchema(array $schema): void
    {
        $this->schemas?->validate($schema);
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
     * The sections to render, in declared order, including those that derive
     * their values from the rest of the head even when never set explicitly.
     *
     * @return array<int, Metadata>
     */
    public function sections(): array
    {
        $sections = [];

        foreach (HeadData::metadata() as $section) {
            $value = $this->section($section);

            if ($value instanceof Metadata) {
                $sections[] = $value;
            } elseif ($section::rendersWhenEmpty($this)) {
                $sections[] = new $section;
            }
        }

        return $sections;
    }

    /**
     * @param  class-string<Metadata>  $section
     */
    public function payload(string $section): mixed
    {
        $value = $this->section($section);

        if (! $value instanceof Metadata && $section::rendersWhenEmpty($this)) {
            $value = new $section;
        }

        return $value?->toPayload($this) ?? $section::payloadDefault();
    }

    /**
     * @param  class-string<Metadata>  $section
     */
    public function section(string $section): ?Metadata
    {
        return $this->data->get($section);
    }
}
