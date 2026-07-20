<?php

declare(strict_types=1);

namespace Laravel\Head\Concerns;

use BackedEnum;
use Illuminate\Contracts\Pagination\Paginator;
use Laravel\Head\Enums\ImageType;
use Laravel\Head\Enums\Media;
use Laravel\Head\Enums\OgType;
use Laravel\Head\Enums\RobotsRule;
use Laravel\Head\Enums\TwitterCard;
use Laravel\Head\HeadData;
use Laravel\Head\Schema\SchemaFactory;
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
use Laravel\Head\Tags\Title;
use Laravel\Head\Tags\Twitter;

trait BuildsHead
{
    /**
     * Get the head data instance the fluent methods should write to.
     */
    abstract protected function headData(): HeadData;

    /**
     * Get the schema factory used to resolve schema callbacks.
     */
    abstract protected function schemaFactory(): SchemaFactory;

    /**
     * Use $exact to render the title without inherited prefix or suffix.
     */
    public function title(string $title, ?string $prefix = null, ?string $suffix = null, ?bool $exact = null): static
    {
        $this->headData()->overlayBuilder(Title::make($title, prefix: $prefix, suffix: $suffix, exact: $exact));

        return $this;
    }

    public function description(string $description): static
    {
        $this->headData()->overlayBuilder(Description::make($description));

        return $this;
    }

    public function themeColor(string $color, Media|string|null $media = null): static
    {
        return $this->meta('theme-color', $color, media: $media);
    }

    public function applicationName(string $name): static
    {
        return $this->meta('application-name', $name);
    }

    public function colorScheme(string $scheme): static
    {
        return $this->meta('color-scheme', $scheme);
    }

    public function referrer(string $policy): static
    {
        return $this->meta('referrer', $policy);
    }

    public function viewport(string $content): static
    {
        return $this->meta('viewport', $content);
    }

    public function appleWebAppTitle(string $title): static
    {
        return $this->meta('apple-mobile-web-app-title', $title);
    }

    public function webAppCapable(bool $capable = true): static
    {
        return $this->meta('mobile-web-app-capable', $capable ? 'yes' : 'no');
    }

    public function appleWebAppStatusBarStyle(string $style): static
    {
        return $this->meta('apple-mobile-web-app-status-bar-style', $style);
    }

    public function canonical(?string $url = null, ?bool $forceHttps = null, ?bool $trailingSlash = null): static
    {
        $this->headData()->overlayBuilder(Canonical::make($url, forceHttps: $forceHttps, trailingSlash: $trailingSlash));

        return $this;
    }

    /**
     * @param  string|RobotsRule|array<int, string|RobotsRule>  $directives
     */
    public function robots(string|RobotsRule|array $directives): static
    {
        $this->headData()->overlayBuilder(Robots::make($directives));

        return $this;
    }

    /**
     * Add a robots meta tag with the "all" directive, allowing search engines to index the page.
     */
    public function searchableByRobots(): static
    {
        return $this->robots(RobotsRule::All);
    }

    /**
     * Add a robots meta tag with the "none" directive, preventing search engines from indexing the page.
     */
    public function hiddenFromRobots(): static
    {
        return $this->robots(RobotsRule::None);
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
        $this->headData()->overlayBuilder(OpenGraph::make(
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

        return $this;
    }

    public function ogImage(
        string $url,
        ?string $alt = null,
        ?int $width = null,
        ?int $height = null,
        ImageType|string|null $type = null,
        ?string $secureUrl = null,
    ): static {
        $this->headData()->builder(OpenGraph::class)->image($url, alt: $alt, width: $width, height: $height, type: $type, secureUrl: $secureUrl);

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
        $this->headData()->builder(OpenGraph::class)->video($url, alt: $alt, width: $width, height: $height, type: $type, secureUrl: $secureUrl);

        return $this;
    }

    public function ogAudio(string $url, ?string $type = null, ?string $secureUrl = null): static
    {
        $this->headData()->builder(OpenGraph::class)->audio($url, type: $type, secureUrl: $secureUrl);

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
        $this->headData()->overlayBuilder(Twitter::make(
            card: $card,
            site: $site,
            creator: $creator,
            title: $title,
            description: $description,
            image: $image,
        ));

        return $this;
    }

    public function twitterImage(string $url, ?string $alt = null): static
    {
        $this->headData()->builder(Twitter::class)->image($url, alt: $alt);

        return $this;
    }

    public function preload(string $href, ?string $as = null, bool|string|null $crossorigin = null, ImageType|string|null $type = null, Media|string|null $media = null): static
    {
        $this->headData()->builder(PerformanceLinks::class)->preload($href, as: $as, crossorigin: $crossorigin, type: $type, media: $media);

        return $this;
    }

    public function preloadAsset(string $path, ?string $as = null, bool|string|null $crossorigin = null, ImageType|string|null $type = null, Media|string|null $media = null): static
    {
        $this->headData()->builder(PerformanceLinks::class)->preloadAsset($path, as: $as, crossorigin: $crossorigin, type: $type, media: $media);

        return $this;
    }

    public function prefetch(string $href, ?string $as = null): static
    {
        $this->headData()->builder(PerformanceLinks::class)->prefetch($href, as: $as);

        return $this;
    }

    public function prefetchAsset(string $path, ?string $as = null): static
    {
        $this->headData()->builder(PerformanceLinks::class)->prefetchAsset($path, as: $as);

        return $this;
    }

    public function preconnect(string $href, bool|string|null $crossorigin = null): static
    {
        $this->headData()->builder(PerformanceLinks::class)->preconnect($href, crossorigin: $crossorigin);

        return $this;
    }

    public function dnsPrefetch(string $href): static
    {
        $this->headData()->builder(PerformanceLinks::class)->dnsPrefetch($href);

        return $this;
    }

    /**
     * Add rel=prev/next links from a paginator instance.
     *
     * @param  Paginator<int, mixed>  $paginator
     */
    public function paginate(Paginator $paginator): static
    {
        $this->headData()->overlayBuilder(PaginationLinks::fromPaginator($paginator));

        return $this;
    }

    /**
     * Add localized alternate URLs.
     *
     * @param  array<string, string>  $alternates
     */
    public function alternates(array $alternates): static
    {
        $this->headData()->overlayBuilder(new AlternateLinks($alternates));

        return $this;
    }

    public function feed(string $href, string $title, string $type = 'rss'): static
    {
        $this->headData()->builder(FeedLinks::class)->feed($href, $title, $type);

        return $this;
    }

    /**
     * Add a JSON-LD schema object to the page.
     *
     * @param  SchemaObject|array<string, mixed>|callable(SchemaFactory): (SchemaObject|array<string, mixed>)  $schema
     */
    public function schema(SchemaObject|array|callable $schema): static
    {
        if (is_callable($schema)) {
            $schema = $schema($this->schemaFactory());
        }

        $this->headData()->builder(Schemas::class)->schema($schema);

        return $this;
    }

    public function meta(string $key, string $content, ?bool $property = null, Media|string|null $media = null): static
    {
        $this->headData()->builder(MetaTags::class)->tag($key, $content, $property, $media);

        return $this;
    }

    /**
     * @param  array<string, BackedEnum|bool|float|int|string|null>  $attributes
     */
    public function link(string $rel, string $href, array $attributes = []): static
    {
        $this->headData()->builder(GenericLinks::class)->link($rel, $href, $attributes);

        return $this;
    }

    public function icon(string $href, ImageType|string|null $type = null, ?string $sizes = null, Media|string|null $media = null): static
    {
        return $this->link('icon', $href, static::linkAttributes([
            'type' => $type instanceof ImageType ? $type->value : $type,
            'sizes' => $sizes,
            'media' => $media instanceof Media ? $media->value : $media,
        ]));
    }

    public function favicon(string $href, ImageType|string|null $type = null, ?string $sizes = null, Media|string|null $media = null): static
    {
        return $this->icon($href, type: $type, sizes: $sizes, media: $media);
    }

    public function appleTouchIcon(string $href, ?string $sizes = null): static
    {
        return $this->link('apple-touch-icon', $href, static::linkAttributes([
            'sizes' => $sizes,
        ]));
    }

    public function maskIcon(string $href, ?string $color = null): static
    {
        return $this->link('mask-icon', $href, static::linkAttributes([
            'color' => $color,
        ]));
    }

    public function manifest(string $href = '/site.webmanifest', bool|string|null $crossorigin = null): static
    {
        return $this->link('manifest', $href, static::linkAttributes([
            'crossorigin' => $crossorigin,
        ]));
    }

    public function appleTouchStartupImage(string $href, Media|string|null $media = null): static
    {
        return $this->link('apple-touch-startup-image', $href, static::linkAttributes([
            'media' => $media instanceof Media ? $media->value : $media,
        ]));
    }

    public function pwa(
        string $name,
        string $manifest = '/site.webmanifest',
        ?string $themeColor = null,
        ?string $appleTouchIcon = null,
        ?string $appleTouchIconSizes = '180x180',
        ?string $appleWebAppStatusBarStyle = null,
    ): static {
        $this->applicationName($name)
            ->appleWebAppTitle($name)
            ->webAppCapable()
            ->manifest($manifest);

        if (! is_null($themeColor)) {
            $this->themeColor($themeColor);
        }

        if (! is_null($appleTouchIcon)) {
            $this->appleTouchIcon($appleTouchIcon, sizes: $appleTouchIconSizes);
        }

        if (! is_null($appleWebAppStatusBarStyle)) {
            $this->appleWebAppStatusBarStyle($appleWebAppStatusBarStyle);
        }

        return $this;
    }

    /**
     * @param  array<string, bool|float|int|string|null>  $attributes
     * @return array<string, bool|float|int|string|null>
     */
    protected static function linkAttributes(array $attributes): array
    {
        return array_filter($attributes, fn ($value) => ! is_null($value));
    }
}
