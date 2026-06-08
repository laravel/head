<?php

declare(strict_types=1);

namespace Laravel\Head;

use Illuminate\Contracts\Pagination\Paginator;
use InvalidArgumentException;
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
use Laravel\Head\Metadata\Robots;
use Laravel\Head\Metadata\Schemas;
use Laravel\Head\Metadata\Title;
use Laravel\Head\Metadata\Twitter;
use Laravel\Head\Schema\SchemaObject;

/**
 * @phpstan-type SchemaPayload array<string, mixed>
 */
class HeadData
{
    /** @var array<class-string<Metadata>, Metadata> */
    protected array $sections = [];

    /** @var array<int, class-string<Metadata>> */
    protected static array $extensions = [];

    public static function base(): self
    {
        return new self;
    }

    /**
     * The metadata sections rendered for every head, in render order.
     *
     * @return array<int, class-string<Metadata>>
     */
    public static function metadata(): array
    {
        return [...static::defaultMetadata(), ...static::$extensions];
    }

    /**
     * Register a custom metadata section.
     *
     * @param  class-string<Metadata>  $section
     */
    public static function extend(string $section): void
    {
        if (! is_subclass_of($section, Metadata::class)) {
            throw new InvalidArgumentException('Head metadata extensions must extend '.Metadata::class.'.');
        }

        if (in_array($section, static::defaultMetadata(), true)) {
            throw new InvalidArgumentException('Built-in head metadata sections are already registered.');
        }

        if (! in_array($section, static::$extensions, true)) {
            static::$extensions[] = $section;
        }
    }

    /**
     * Forget every registered custom metadata section.
     */
    public static function flushExtensions(): void
    {
        static::$extensions = [];
    }

    /**
     * @return array<int, class-string<Metadata>>
     */
    protected static function defaultMetadata(): array
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

    /**
     * @return array<string, class-string<Metadata>>
     */
    public static function attributeKeys(): array
    {
        $keys = [];

        foreach (static::metadata() as $metadata) {
            foreach ($metadata::attributeKeys() as $key) {
                $keys[$key] = $metadata;
            }
        }

        return $keys;
    }

    /**
     * @return array<class-string<Metadata>, Metadata>
     */
    public function sections(): array
    {
        return $this->sections;
    }

    /**
     * @param  class-string<Metadata>  $section
     */
    public function get(string $section): ?Metadata
    {
        return $this->sections[$section] ?? null;
    }

    public function set(Metadata $section): static
    {
        $class = $section::class;
        $this->sections[$class] = $section->overlay($this->sections[$class] ?? null);

        return $this;
    }

    public function merge(HeadData $data): self
    {
        $merged = clone $this;

        foreach ($data->sections as $section) {
            $merged->set($section);
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
        foreach ($this->sections as $class => $section) {
            $this->sections[$class] = $section->asDefaults();
        }

        return $this;
    }

    public function title(string $title, ?string $prefix = null, ?string $suffix = null, ?bool $bare = null): static
    {
        return $this->set(Title::make($title, prefix: $prefix, suffix: $suffix, bare: $bare));
    }

    public function description(string $description): static
    {
        return $this->set(Description::make($description));
    }

    public function canonical(string|false|null $url = null, ?bool $forceHttps = null, ?bool $trailingSlash = null): static
    {
        return $this->set(Canonical::make($url, forceHttps: $forceHttps, trailingSlash: $trailingSlash));
    }

    public function robots(string $directives): static
    {
        return $this->set(Robots::make($directives));
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
        return $this->set(OpenGraph::make(
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
        return $this->set(Twitter::make(
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
        return $this->set(PaginationLinks::fromPaginator($paginator));
    }

    /**
     * @param  array<string, string>  $alternates
     */
    public function alternates(array $alternates): static
    {
        return $this->set(AlternateLinks::fromAttributes($alternates));
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
     * @param  SchemaObject|SchemaPayload  $schema
     */
    public function schema(SchemaObject|array $schema): static
    {
        $this->section(Schemas::class)->schema($schema);

        return $this;
    }

    /**
     * Resolve a mutable section instance, creating and storing it on first use.
     *
     * @template TMetadata of Metadata
     *
     * @param  class-string<TMetadata>  $class
     * @return TMetadata
     */
    protected function section(string $class): Metadata
    {
        /** @var TMetadata */
        return $this->sections[$class] ??= new $class;
    }
}
