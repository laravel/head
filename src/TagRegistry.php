<?php

declare(strict_types=1);

namespace Laravel\Head;

use InvalidArgumentException;
use Laravel\Head\Tags\AlternateLinks;
use Laravel\Head\Tags\Canonical;
use Laravel\Head\Tags\Description;
use Laravel\Head\Tags\FeedLinks;
use Laravel\Head\Tags\GenericLinks;
use Laravel\Head\Tags\MetaTags;
use Laravel\Head\Tags\OpenGraph;
use Laravel\Head\Tags\PaginationLinks;
use Laravel\Head\Tags\PerformanceLinks;
use Laravel\Head\Tags\Robots;
use Laravel\Head\Tags\Schemas;
use Laravel\Head\Tags\TagBuilder;
use Laravel\Head\Tags\Title;
use Laravel\Head\Tags\Twitter;

class TagRegistry
{
    /** @var array<int, class-string<TagBuilder>> */
    protected array $extensions = [];

    /**
     * The tag builders rendered for every head, in render order.
     *
     * @return array<int, class-string<TagBuilder>>
     */
    public function builders(): array
    {
        return [...$this->defaults(), ...$this->extensions];
    }

    /**
     * Register a custom tag builder.
     *
     * @param  class-string  $builder
     */
    public function extend(string $builder): static
    {
        if (! is_subclass_of($builder, TagBuilder::class)) {
            throw new InvalidArgumentException('Head tag extensions must extend '.TagBuilder::class.'.');
        }

        if (in_array($builder, $this->defaults(), true)) {
            throw new InvalidArgumentException('Built-in head tag builders are already registered.');
        }

        $duplicates = array_values(array_intersect($builder::routeAttributeKeys(), array_keys($this->routeAttributeKeys())));

        if ($duplicates !== []) {
            throw new InvalidArgumentException(sprintf(
                'Head tag extensions may not register route attributes already registered by another builder: %s.',
                implode(', ', $duplicates),
            ));
        }

        if (! in_array($builder, $this->extensions, true)) {
            $this->extensions[] = $builder;
        }

        return $this;
    }

    /**
     * @return array<string, class-string<TagBuilder>>
     */
    public function routeAttributeKeys(): array
    {
        $keys = [];

        foreach ($this->builders() as $builder) {
            foreach ($builder::routeAttributeKeys() as $key) {
                $keys[$key] = $builder;
            }
        }

        return $keys;
    }

    /**
     * @return array<int, class-string<TagBuilder>>
     */
    protected function defaults(): array
    {
        return [
            Title::class,
            Description::class,
            Canonical::class,
            Robots::class,
            OpenGraph::class,
            Twitter::class,
            PaginationLinks::class,
            AlternateLinks::class,
            FeedLinks::class,
            PerformanceLinks::class,
            MetaTags::class,
            GenericLinks::class,
            Schemas::class,
        ];
    }
}
