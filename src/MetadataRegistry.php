<?php

declare(strict_types=1);

namespace Laravel\Head;

use InvalidArgumentException;
use Laravel\Head\Metadata\AlternateLinks;
use Laravel\Head\Metadata\Canonical;
use Laravel\Head\Metadata\Description;
use Laravel\Head\Metadata\FeedLinks;
use Laravel\Head\Metadata\GenericLinks;
use Laravel\Head\Metadata\MetaTags;
use Laravel\Head\Metadata\OpenGraph;
use Laravel\Head\Metadata\PaginationLinks;
use Laravel\Head\Metadata\PerformanceLinks;
use Laravel\Head\Metadata\Robots;
use Laravel\Head\Metadata\Schemas;
use Laravel\Head\Metadata\Section;
use Laravel\Head\Metadata\Title;
use Laravel\Head\Metadata\Twitter;

class MetadataRegistry
{
    /** @var array<int, class-string<Section>> */
    protected array $extensions = [];

    /**
     * The metadata sections rendered for every head, in render order.
     *
     * @return array<int, class-string<Section>>
     */
    public function sections(): array
    {
        return [...$this->defaults(), ...$this->extensions];
    }

    /**
     * Register a custom metadata section.
     *
     * @param  class-string<Section>  $section
     */
    public function extend(string $section): static
    {
        if (! is_subclass_of($section, Section::class)) {
            throw new InvalidArgumentException('Head metadata extensions must extend '.Section::class.'.');
        }

        if (in_array($section, $this->defaults(), true)) {
            throw new InvalidArgumentException('Built-in head metadata sections are already registered.');
        }

        if (! in_array($section, $this->extensions, true)) {
            $this->extensions[] = $section;
        }

        return $this;
    }

    /**
     * @return array<string, class-string<Section>>
     */
    public function attributeKeys(): array
    {
        $keys = [];

        foreach ($this->sections() as $section) {
            foreach ($section::attributeKeys() as $key) {
                $keys[$key] = $section;
            }
        }

        return $keys;
    }

    /**
     * @return array<int, class-string<Section>>
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
