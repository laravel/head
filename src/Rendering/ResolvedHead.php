<?php

declare(strict_types=1);

namespace Laravel\Head\Rendering;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Head\HeadData;
use Laravel\Head\Metadata\Canonical;
use Laravel\Head\Metadata\Description;
use Laravel\Head\Metadata\OpenGraph;
use Laravel\Head\Metadata\Robots;
use Laravel\Head\Metadata\Section;
use Laravel\Head\Metadata\Title;
use Laravel\Head\MetadataRegistry;
use Laravel\Head\Schema\SchemaValidator;

class ResolvedHead
{
    public function __construct(
        protected HeadData $data,
        protected MetadataRegistry $registry,
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
     * @return array<string, string>|null
     */
    public function openGraphImage(): ?array
    {
        $openGraph = $this->section(OpenGraph::class);

        if (! $openGraph instanceof OpenGraph || ($images = $openGraph->images()) === []) {
            return null;
        }

        $image = reset($images);

        return Arr::whereNotNull([
            'url' => $image['url'],
            'alt' => $image['alt'] ?? null,
        ]);
    }

    /**
     * The sections to render, in declared order, including those that derive
     * their values from the rest of the head even when never set explicitly.
     *
     * @return array<int, Section>
     */
    public function sections(): array
    {
        $sections = [];

        foreach ($this->registry->sections() as $section) {
            $value = $this->section($section);

            if ($value instanceof Section) {
                $sections[] = $value;
            } elseif ($section::rendersWhenEmpty($this)) {
                $sections[] = new $section;
            }
        }

        return $sections;
    }

    /**
     * Resolve one metadata section into its Head::toArray() / Inertia head array
     * value, including section defaults for metadata that was never set.
     *
     * @param  class-string<Section>  $section
     */
    public function headArray(string $section): mixed
    {
        $value = $this->section($section);

        if (! $value instanceof Section && $section::rendersWhenEmpty($this)) {
            $value = new $section;
        }

        return $value?->toHeadArray($this) ?? $section::headArrayDefault();
    }

    /**
     * @param  class-string<Section>  $section
     */
    public function section(string $section): ?Section
    {
        return $this->data->get($section);
    }
}
