<?php

declare(strict_types=1);

namespace Laravel\Head;

use Illuminate\Contracts\Pagination\Paginator;
use Laravel\Head\Schema\SchemaObject;
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

/**
 * @phpstan-type SchemaData array<string, mixed>
 */
class HeadData
{
    /** @var array<class-string<TagBuilder>, TagBuilder> */
    protected array $builders = [];

    /**
     * @return array<class-string<TagBuilder>, TagBuilder>
     */
    public function builders(): array
    {
        return $this->builders;
    }

    /**
     * @param  class-string<TagBuilder>  $builder
     */
    public function get(string $builder): ?TagBuilder
    {
        return $this->builders[$builder] ?? null;
    }

    public function overlayBuilder(TagBuilder $builder): static
    {
        $class = $builder::class;
        $this->builders[$class] = $builder->overlayOn($this->builders[$class] ?? null);

        return $this;
    }

    public function merge(HeadData $data): self
    {
        $merged = clone $this;

        foreach ($data->builders as $builder) {
            $merged->overlayBuilder($builder);
        }

        return $merged;
    }

    public function isEmpty(): bool
    {
        foreach ($this->builders as $builder) {
            if (! $builder->isEmpty()) {
                return false;
            }
        }

        return true;
    }

    public function asDefaults(): static
    {
        $defaults = clone $this;

        foreach ($defaults->builders as $class => $builder) {
            $defaults->builders[$class] = $builder->asDefaults();
        }

        return $defaults;
    }

    public function title(string $title, ?string $prefix = null, ?string $suffix = null, ?bool $bare = null): static
    {
        return $this->overlayBuilder(Title::make($title, prefix: $prefix, suffix: $suffix, bare: $bare));
    }

    public function description(string $description): static
    {
        return $this->overlayBuilder(Description::make($description));
    }

    public function canonical(string|false|null $url = null, ?bool $forceHttps = null, ?bool $trailingSlash = null): static
    {
        return $this->overlayBuilder(Canonical::make($url, forceHttps: $forceHttps, trailingSlash: $trailingSlash));
    }

    public function robots(string $directives): static
    {
        return $this->overlayBuilder(Robots::make($directives));
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
        return $this->overlayBuilder(OpenGraph::make(
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
        $this->builder(OpenGraph::class)->image($url, alt: $alt, width: $width, height: $height, type: $type, secureUrl: $secureUrl);

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
        $this->builder(OpenGraph::class)->video($url, alt: $alt, width: $width, height: $height, type: $type, secureUrl: $secureUrl);

        return $this;
    }

    public function ogAudio(string $url, ?string $type = null, ?string $secureUrl = null): static
    {
        $this->builder(OpenGraph::class)->audio($url, type: $type, secureUrl: $secureUrl);

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
        return $this->overlayBuilder(Twitter::make(
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
        $this->builder(Twitter::class)->image($url, alt: $alt);

        return $this;
    }

    public function preload(string $href, ?string $as = null, bool|string|null $crossorigin = null, ?string $type = null, ?string $media = null): static
    {
        $this->builder(PerformanceLinks::class)->preload($href, as: $as, crossorigin: $crossorigin, type: $type, media: $media);

        return $this;
    }

    public function prefetch(string $href, ?string $as = null): static
    {
        $this->builder(PerformanceLinks::class)->prefetch($href, as: $as);

        return $this;
    }

    public function preconnect(string $href, bool|string|null $crossorigin = null): static
    {
        $this->builder(PerformanceLinks::class)->preconnect($href, crossorigin: $crossorigin);

        return $this;
    }

    public function dnsPrefetch(string $href): static
    {
        $this->builder(PerformanceLinks::class)->dnsPrefetch($href);

        return $this;
    }

    /**
     * @param  Paginator<int, mixed>  $paginator
     */
    public function paginate(Paginator $paginator): static
    {
        return $this->overlayBuilder(PaginationLinks::fromPaginator($paginator));
    }

    /**
     * @param  array<string, string>  $alternates
     */
    public function alternates(array $alternates): static
    {
        return $this->overlayBuilder(AlternateLinks::fromAttributes($alternates));
    }

    public function feed(string $href, string $title, string $type = 'rss'): static
    {
        $this->builder(FeedLinks::class)->feed($href, $title, $type);

        return $this;
    }

    public function meta(string $key, string $content, ?bool $property = null): static
    {
        $this->builder(MetaTags::class)->tag($key, $content, $property);

        return $this;
    }

    /**
     * @param  array<string, bool|float|int|string|null>  $attributes
     */
    public function link(string $rel, string $href, array $attributes = []): static
    {
        $this->builder(GenericLinks::class)->link($rel, $href, $attributes);

        return $this;
    }

    /**
     * @param  SchemaObject|SchemaData  $schema
     */
    public function schema(SchemaObject|array $schema): static
    {
        $this->builder(Schemas::class)->schema($schema);

        return $this;
    }

    /**
     * Resolve a mutable tag builder instance, creating and storing it on first use.
     *
     * @template TBuilder of TagBuilder
     *
     * @param  class-string<TBuilder>  $class
     * @return TBuilder
     */
    protected function builder(string $class): TagBuilder
    {
        /** @var TBuilder */
        return $this->builders[$class] ??= new $class;
    }
}
