<?php

declare(strict_types=1);

namespace Laravel\Head\Rendering;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Head\HeadData;
use Laravel\Head\Schema\SchemaValidator;
use Laravel\Head\TagRegistry;
use Laravel\Head\Tags\Canonical;
use Laravel\Head\Tags\Description;
use Laravel\Head\Tags\OpenGraph;
use Laravel\Head\Tags\Robots;
use Laravel\Head\Tags\TagBuilder;
use Laravel\Head\Tags\Title;

class ResolvedHead
{
    public function __construct(
        protected HeadData $data,
        protected TagRegistry $registry,
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
        $title = $this->builder(Title::class);

        return $title instanceof Title ? $title->render() : null;
    }

    public function description(): ?string
    {
        $description = $this->builder(Description::class);

        return $description instanceof Description ? $description->render() : null;
    }

    public function canonical(): ?string
    {
        $canonical = $this->builder(Canonical::class);

        return $canonical instanceof Canonical ? $canonical->render($this->request) : null;
    }

    public function robots(): ?string
    {
        $robots = $this->builder(Robots::class);

        return $robots instanceof Robots ? $robots->render() : null;
    }

    /**
     * @return array<string, string>|null
     */
    public function openGraphImage(): ?array
    {
        $openGraph = $this->builder(OpenGraph::class);

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
     * The tag builders to render, in declared order, including those that derive
     * their values from the rest of the head even when never set explicitly.
     *
     * @return array<int, TagBuilder>
     */
    public function builders(): array
    {
        $builders = [];

        foreach ($this->registry->builders() as $builder) {
            $value = $this->builder($builder);

            if ($value instanceof TagBuilder) {
                $builders[] = $value;
            } elseif ($builder::rendersWhenEmpty($this)) {
                $builders[] = new $builder;
            }
        }

        return $builders;
    }

    /**
     * Resolve one tag builder into its Head::toArray() value,
     * including defaults for builder data that was never set.
     *
     * @param  class-string<TagBuilder>  $builder
     */
    public function headArray(string $builder): mixed
    {
        $value = $this->builder($builder);

        if (! $value instanceof TagBuilder && $builder::rendersWhenEmpty($this)) {
            $value = new $builder;
        }

        return $value?->toHeadArray($this) ?? $builder::headArrayDefault();
    }

    /**
     * @param  class-string<TagBuilder>  $builder
     */
    public function builder(string $builder): ?TagBuilder
    {
        return $this->data->get($builder);
    }
}
