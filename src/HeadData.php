<?php

declare(strict_types=1);

namespace Laravel\Head;

use Illuminate\Contracts\Pagination\Paginator;
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
use Laravel\Head\Schema\SchemaObject;

/**
 * @phpstan-type SchemaData array<string, mixed>
 */
class HeadData
{
    /** @var array<class-string<Section>, Section> */
    protected array $sections = [];

    /**
     * @return array<class-string<Section>, Section>
     */
    public function sections(): array
    {
        return $this->sections;
    }

    /**
     * @param  class-string<Section>  $section
     */
    public function get(string $section): ?Section
    {
        return $this->sections[$section] ?? null;
    }

    public function overlaySection(Section $section): static
    {
        $class = $section::class;
        $this->sections[$class] = $section->overlayOn($this->sections[$class] ?? null);

        return $this;
    }

    public function merge(HeadData $data): self
    {
        $merged = clone $this;

        foreach ($data->sections as $section) {
            $merged->overlaySection($section);
        }

        return $merged;
    }

    public function isEmpty(): bool
    {
        foreach ($this->sections as $section) {
            if (! $section->isEmpty()) {
                return false;
            }
        }

        return true;
    }

    public function asDefaults(): static
    {
        $defaults = clone $this;

        foreach ($defaults->sections as $class => $section) {
            $defaults->sections[$class] = $section->asDefaults();
        }

        return $defaults;
    }

    public function title(string $title, ?string $prefix = null, ?string $suffix = null, ?bool $bare = null): static
    {
        return $this->overlaySection(Title::make($title, prefix: $prefix, suffix: $suffix, bare: $bare));
    }

    public function description(string $description): static
    {
        return $this->overlaySection(Description::make($description));
    }

    public function canonical(string|false|null $url = null, ?bool $forceHttps = null, ?bool $trailingSlash = null): static
    {
        return $this->overlaySection(Canonical::make($url, forceHttps: $forceHttps, trailingSlash: $trailingSlash));
    }

    public function robots(string $directives): static
    {
        return $this->overlaySection(Robots::make($directives));
    }

    public function og(
        OgType|string|null $type = null,
        ?string $title = null,
        ?string $description = null,
        ?string $url = null,
        ?string $image = null,
        ?string $video = null,
        ?string $audio = null,
        ?string $siteName = null,
        ?string $locale = null,
        ?string $determiner = null,
    ): static {
        return $this->overlaySection(OpenGraph::make(
            type: $type,
            title: $title,
            description: $description,
            url: $url,
            image: $image,
            video: $video,
            audio: $audio,
            siteName: $siteName,
            locale: $locale,
            determiner: $determiner,
        ));
    }

    public function ogImage(
        string $url,
        ?string $alt = null,
        ?int $width = null,
        ?int $height = null,
        ?string $type = null,
        ?string $secureUrl = null,
    ): static {
        $this->section(OpenGraph::class)->image($url, alt: $alt, width: $width, height: $height, type: $type, secureUrl: $secureUrl);

        return $this;
    }

    public function ogVideo(
        string $url,
        ?string $alt = null,
        ?int $width = null,
        ?int $height = null,
        ?string $type = null,
        ?string $secureUrl = null,
    ): static {
        $this->section(OpenGraph::class)->video($url, alt: $alt, width: $width, height: $height, type: $type, secureUrl: $secureUrl);

        return $this;
    }

    public function ogAudio(string $url, ?string $type = null, ?string $secureUrl = null): static
    {
        $this->section(OpenGraph::class)->audio($url, type: $type, secureUrl: $secureUrl);

        return $this;
    }

    public function twitter(
        TwitterCard|string|null $card = null,
        ?string $site = null,
        ?string $creator = null,
        ?string $title = null,
        ?string $description = null,
        ?string $image = null,
    ): static {
        return $this->overlaySection(Twitter::make(
            card: $card,
            site: $site,
            creator: $creator,
            title: $title,
            description: $description,
            image: $image,
        ));
    }

    public function twitterImage(string $url, ?string $alt = null): static
    {
        $this->section(Twitter::class)->image($url, alt: $alt);

        return $this;
    }

    public function preload(string $href, ?string $as = null, bool|string|null $crossorigin = null, ?string $type = null, ?string $media = null): static
    {
        $this->section(PerformanceLinks::class)->preload($href, as: $as, crossorigin: $crossorigin, type: $type, media: $media);

        return $this;
    }

    public function prefetch(string $href, ?string $as = null): static
    {
        $this->section(PerformanceLinks::class)->prefetch($href, as: $as);

        return $this;
    }

    public function preconnect(string $href, bool|string|null $crossorigin = null): static
    {
        $this->section(PerformanceLinks::class)->preconnect($href, crossorigin: $crossorigin);

        return $this;
    }

    public function dnsPrefetch(string $href): static
    {
        $this->section(PerformanceLinks::class)->dnsPrefetch($href);

        return $this;
    }

    /**
     * @param  Paginator<int, mixed>  $paginator
     */
    public function paginate(Paginator $paginator): static
    {
        return $this->overlaySection(PaginationLinks::fromPaginator($paginator));
    }

    /**
     * @param  array<string, string>  $alternates
     */
    public function alternates(array $alternates): static
    {
        return $this->overlaySection(AlternateLinks::fromAttributes($alternates));
    }

    public function feed(string $href, string $title, string $type = 'rss'): static
    {
        $this->section(FeedLinks::class)->feed($href, $title, $type);

        return $this;
    }

    public function meta(string $key, string $content, ?bool $property = null): static
    {
        $this->section(MetaTags::class)->tag($key, $content, $property);

        return $this;
    }

    /**
     * @param  array<string, bool|float|int|string|null>  $attributes
     */
    public function link(string $rel, string $href, array $attributes = []): static
    {
        $this->section(GenericLinks::class)->link($rel, $href, $attributes);

        return $this;
    }

    /**
     * @param  SchemaObject|SchemaData  $schema
     */
    public function schema(SchemaObject|array $schema): static
    {
        $this->section(Schemas::class)->schema($schema);

        return $this;
    }

    /**
     * Resolve a mutable section instance, creating and storing it on first use.
     *
     * @template TSection of Section
     *
     * @param  class-string<TSection>  $class
     * @return TSection
     */
    protected function section(string $class): Section
    {
        /** @var TSection */
        return $this->sections[$class] ??= new $class;
    }
}
