<?php

declare(strict_types=1);

namespace Laravel\Head;

use Illuminate\Contracts\Pagination\Paginator;
use Laravel\Head\Schema\SchemaObject;

/**
 * @phpstan-type FeedAttributes array{href: string, title: string, type: string}
 * @phpstan-type LinkAttributes array{href: string, as?: string|null, crossorigin?: bool|string|null, type?: string|null, media?: string|null}
 * @phpstan-type MediaAttributes array{url: string, alt?: string|null, width?: int|null, height?: int|null, type?: string|null, secure_url?: string|null}
 * @phpstan-type MetaAttributes array{key: string, content: string, property?: bool|null}
 * @phpstan-type GenericLinkAttributes array{rel: string, href: string, attributes: array<string, bool|float|int|string|null>}
 * @phpstan-type SchemaPayload array<string, mixed>
 */
class HeadData
{
    public ?string $title = null;

    public ?bool $titleDecorated = null;

    public ?string $titlePrefix = null;

    public ?string $titleSuffix = null;

    public ?string $description = null;

    public ?string $canonicalUrl = null;

    public ?string $canonicalMode = null;

    public ?bool $canonicalForceHttps = null;

    public ?bool $canonicalTrailingSlash = null;

    public ?string $robots = null;

    /** @var array<string, string> */
    public array $openGraph = [];

    /** @var array<string, MediaAttributes> */
    public array $openGraphImages = [];

    /** @var array<string, MediaAttributes> */
    public array $openGraphVideos = [];

    /** @var array<string, MediaAttributes> */
    public array $openGraphAudios = [];

    /** @var array<string, string> */
    public array $twitter = [];

    /** @var array{url: string, alt?: string|null}|null */
    public ?array $twitterImage = null;

    /** @var array<string, LinkAttributes> */
    public array $preloads = [];

    /** @var array<string, LinkAttributes> */
    public array $prefetches = [];

    /** @var array<string, LinkAttributes> */
    public array $preconnects = [];

    /** @var array<string, array{href: string}> */
    public array $dnsPrefetches = [];

    /** @var array<string, string> */
    public array $paginationLinks = [];

    /** @var array<string, string> */
    public array $alternates = [];

    /** @var array<string, FeedAttributes> */
    public array $feeds = [];

    /** @var array<string, MetaAttributes> */
    public array $meta = [];

    /** @var array<string, GenericLinkAttributes> */
    public array $genericLinks = [];

    /** @var array<string, SchemaObject|SchemaPayload> */
    public array $schemas = [];

    public static function base(): self
    {
        $head = new self;

        $head->canonicalMode = 'auto';
        $head->canonicalForceHttps = true;
        $head->canonicalTrailingSlash = false;
        $head->robots = 'index, follow';

        return $head;
    }

    public function merge(HeadData $data): self
    {
        $merged = clone $this;

        $merged->title = $data->title ?? $merged->title;
        $merged->titleDecorated = $data->titleDecorated ?? $merged->titleDecorated;
        $merged->titlePrefix = $data->titlePrefix ?? $merged->titlePrefix;
        $merged->titleSuffix = $data->titleSuffix ?? $merged->titleSuffix;
        $merged->description = $data->description ?? $merged->description;
        $merged->canonicalUrl = $data->canonicalUrl ?? $merged->canonicalUrl;
        $merged->canonicalMode = $data->canonicalMode ?? $merged->canonicalMode;
        $merged->canonicalForceHttps = $data->canonicalForceHttps ?? $merged->canonicalForceHttps;
        $merged->canonicalTrailingSlash = $data->canonicalTrailingSlash ?? $merged->canonicalTrailingSlash;
        $merged->robots = $data->robots ?? $merged->robots;

        $merged->openGraph = array_replace($merged->openGraph, $data->openGraph);
        $merged->openGraphImages = array_replace($merged->openGraphImages, $data->openGraphImages);
        $merged->openGraphVideos = array_replace($merged->openGraphVideos, $data->openGraphVideos);
        $merged->openGraphAudios = array_replace($merged->openGraphAudios, $data->openGraphAudios);
        $merged->twitter = array_replace($merged->twitter, $data->twitter);
        $merged->twitterImage = $data->twitterImage ?? $merged->twitterImage;
        $merged->preloads = array_replace($merged->preloads, $data->preloads);
        $merged->prefetches = array_replace($merged->prefetches, $data->prefetches);
        $merged->preconnects = array_replace($merged->preconnects, $data->preconnects);
        $merged->dnsPrefetches = array_replace($merged->dnsPrefetches, $data->dnsPrefetches);
        $merged->paginationLinks = array_replace($merged->paginationLinks, $data->paginationLinks);
        $merged->alternates = array_replace($merged->alternates, $data->alternates);
        $merged->feeds = array_replace($merged->feeds, $data->feeds);
        $merged->meta = array_replace($merged->meta, $data->meta);
        $merged->genericLinks = array_replace($merged->genericLinks, $data->genericLinks);
        $merged->schemas = array_replace($merged->schemas, $data->schemas);

        return $merged;
    }

    public function isEmpty(): bool
    {
        return is_null($this->title)
            && is_null($this->titleDecorated)
            && is_null($this->titlePrefix)
            && is_null($this->titleSuffix)
            && is_null($this->description)
            && is_null($this->canonicalUrl)
            && is_null($this->canonicalMode)
            && is_null($this->canonicalForceHttps)
            && is_null($this->canonicalTrailingSlash)
            && is_null($this->robots)
            && $this->openGraph === []
            && $this->openGraphImages === []
            && $this->openGraphVideos === []
            && $this->openGraphAudios === []
            && $this->twitter === []
            && is_null($this->twitterImage)
            && $this->preloads === []
            && $this->prefetches === []
            && $this->preconnects === []
            && $this->dnsPrefetches === []
            && $this->paginationLinks === []
            && $this->alternates === []
            && $this->feeds === []
            && $this->meta === []
            && $this->genericLinks === []
            && $this->schemas === [];
    }

    public function asDefaults(): static
    {
        if (! is_null($this->title)) {
            $this->titleDecorated = false;
        }

        return $this;
    }

    public function title(string $title, ?string $prefix = null, ?string $suffix = null, ?bool $bare = null): static
    {
        $this->title = $title;
        $this->titleDecorated = ! ($bare ?? false);

        if (! is_null($prefix)) {
            $this->titlePrefix = $prefix;
        }

        if (! is_null($suffix)) {
            $this->titleSuffix = $suffix;
        }

        return $this;
    }

    public function description(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function canonical(string|CanonicalMode $value, ?bool $forceHttps = null, ?bool $trailingSlash = null): static
    {
        if ($value instanceof CanonicalMode) {
            $this->canonicalMode = match ($value) {
                CanonicalMode::Auto => 'auto',
                CanonicalMode::None => 'none',
            };

            $this->canonicalUrl = null;
        } else {
            $this->canonicalMode = 'url';
            $this->canonicalUrl = $value;
        }

        if (! is_null($forceHttps)) {
            $this->canonicalForceHttps = $forceHttps;
        }

        if (! is_null($trailingSlash)) {
            $this->canonicalTrailingSlash = $trailingSlash;
        }

        return $this;
    }

    public function robots(string $directives): static
    {
        $this->robots = $directives;

        return $this;
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
        $this->setOpenGraph([
            'type' => $type instanceof OgType ? $type->value : $type,
            'title' => $title,
            'description' => $description,
            'url' => $url,
            'site_name' => $siteName,
            'locale' => $locale,
            'determiner' => $determiner,
        ]);

        if (! is_null($image)) {
            $this->ogImage($image);
        }

        if (! is_null($video)) {
            $this->ogVideo($video);
        }

        if (! is_null($audio)) {
            $this->ogAudio($audio);
        }

        return $this;
    }

    public function ogImage(
        string $url,
        ?string $alt = null,
        ?int $width = null,
        ?int $height = null,
        ?string $type = null,
        ?string $secureUrl = null,
    ): static {
        $this->openGraphImages[$url] = $this->media($url, $alt, $width, $height, $type, $secureUrl);

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
        $this->openGraphVideos[$url] = $this->media($url, $alt, $width, $height, $type, $secureUrl);

        return $this;
    }

    public function ogAudio(string $url, ?string $type = null, ?string $secureUrl = null): static
    {
        $this->openGraphAudios[$url] = $this->media($url, type: $type, secureUrl: $secureUrl);

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
        $this->setTwitter([
            'card' => $card instanceof TwitterCard ? $card->value : $card,
            'site' => $site,
            'creator' => $creator,
            'title' => $title,
            'description' => $description,
        ]);

        if (! is_null($image)) {
            $this->twitterImage($image);
        }

        return $this;
    }

    public function twitterImage(string $url, ?string $alt = null): static
    {
        $this->twitterImage = array_filter([
            'url' => $url,
            'alt' => $alt,
        ], fn (mixed $value): bool => ! is_null($value));

        return $this;
    }

    public function preload(string $href, ?string $as = null, bool|string|null $crossorigin = null, ?string $type = null, ?string $media = null): static
    {
        $this->preloads[$href] = array_filter([
            'href' => $href,
            'as' => $as,
            'crossorigin' => $crossorigin,
            'type' => $type,
            'media' => $media,
        ], fn (mixed $value): bool => ! is_null($value));

        return $this;
    }

    public function prefetch(string $href, ?string $as = null): static
    {
        $this->prefetches[$href] = array_filter([
            'href' => $href,
            'as' => $as,
        ], fn (mixed $value): bool => ! is_null($value));

        return $this;
    }

    public function preconnect(string $href, bool|string|null $crossorigin = null): static
    {
        $this->preconnects[$href] = array_filter([
            'href' => $href,
            'crossorigin' => $crossorigin,
        ], fn (mixed $value): bool => ! is_null($value));

        return $this;
    }

    public function dnsPrefetch(string $href): static
    {
        $this->dnsPrefetches[$href] = ['href' => $href];

        return $this;
    }

    /**
     * @param  Paginator<int, mixed>  $paginator
     */
    public function paginate(Paginator $paginator): static
    {
        if ($previous = $paginator->previousPageUrl()) {
            $this->paginationLinks['prev'] = $previous;
        }

        if ($next = $paginator->nextPageUrl()) {
            $this->paginationLinks['next'] = $next;
        }

        return $this;
    }

    /**
     * @param  array<string, string>  $alternates
     */
    public function alternates(array $alternates): static
    {
        foreach ($alternates as $locale => $href) {
            $this->alternates[(string) $locale] = $href;
        }

        return $this;
    }

    public function feed(string $href, string $title, string $type = 'rss'): static
    {
        $this->feeds[$href] = [
            'href' => $href,
            'title' => $title,
            'type' => $type,
        ];

        return $this;
    }

    public function meta(string $key, string $content, ?bool $property = null): static
    {
        $this->meta[$key] = array_filter([
            'key' => $key,
            'content' => $content,
            'property' => $property,
        ], fn (mixed $value): bool => ! is_null($value));

        return $this;
    }

    /**
     * @param  array<string, bool|float|int|string|null>  $attributes
     */
    public function link(string $rel, string $href, array $attributes = []): static
    {
        unset($attributes['rel'], $attributes['href']);

        $this->genericLinks[$rel.' '.$href] = [
            'rel' => $rel,
            'href' => $href,
            'attributes' => $attributes,
        ];

        return $this;
    }

    /**
     * @param  SchemaObject|SchemaPayload  $schema
     */
    public function schema(SchemaObject|array $schema): static
    {
        $this->schemas[$schema instanceof SchemaObject ? spl_object_hash($schema) : md5(json_encode($schema, JSON_THROW_ON_ERROR))] = $schema;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function fill(array $values): static
    {
        if (array_key_exists('title', $values)) {
            $this->fillTitle($values['title']);
        }

        if (array_key_exists('description', $values) && is_string($values['description'])) {
            $this->description($values['description']);
        }

        if (array_key_exists('canonical', $values)) {
            $this->fillCanonical($values['canonical']);
        }

        if (array_key_exists('robots', $values) && is_string($values['robots'])) {
            $this->robots($values['robots']);
        }

        if (array_key_exists('og', $values) && is_array($values['og'])) {
            $this->fillOpenGraph($values['og']);
        }

        if (array_key_exists('ogImage', $values)) {
            $this->fillOpenGraphImages($values['ogImage']);
        }

        if (array_key_exists('ogVideo', $values)) {
            $this->fillOpenGraphVideos($values['ogVideo']);
        }

        if (array_key_exists('ogAudio', $values)) {
            $this->fillOpenGraphAudios($values['ogAudio']);
        }

        if (array_key_exists('twitter', $values) && is_array($values['twitter'])) {
            $this->fillTwitter($values['twitter']);
        }

        if (array_key_exists('twitterImage', $values)) {
            $this->fillTwitterImage($values['twitterImage']);
        }

        if (array_key_exists('preload', $values) && is_array($values['preload'])) {
            $this->fillPreloads($values['preload']);
        }

        if (array_key_exists('prefetch', $values) && is_array($values['prefetch'])) {
            $this->fillPrefetches($values['prefetch']);
        }

        if (array_key_exists('preconnect', $values) && is_array($values['preconnect'])) {
            $this->fillPreconnects($values['preconnect']);
        }

        if (array_key_exists('dnsPrefetch', $values) && is_array($values['dnsPrefetch'])) {
            $this->fillDnsPrefetches($values['dnsPrefetch']);
        }

        if (array_key_exists('alternates', $values) && is_array($values['alternates'])) {
            $this->fillAlternates($values['alternates']);
        }

        if (array_key_exists('feed', $values) && is_array($values['feed'])) {
            $this->fillFeeds($values['feed']);
        }

        if (array_key_exists('meta', $values) && is_array($values['meta'])) {
            $this->fillMeta($values['meta']);
        }

        if (array_key_exists('link', $values) && is_array($values['link'])) {
            $this->fillLinks($values['link']);
        }

        if (array_key_exists('schema', $values) && ($values['schema'] instanceof SchemaObject || is_array($values['schema']))) {
            $this->fillSchemas($values['schema']);
        }

        return $this;
    }

    protected function fillTitle(mixed $title): void
    {
        if (is_string($title)) {
            $this->title($title);

            return;
        }

        if (! is_array($title) || ! is_string($title['value'] ?? null)) {
            return;
        }

        $this->title(
            $title['value'],
            prefix: $this->string($title['prefix'] ?? null),
            suffix: $this->string($title['suffix'] ?? null),
            bare: $this->bool($title['bare'] ?? null),
        );
    }

    protected function fillCanonical(mixed $canonical): void
    {
        if (is_array($canonical)) {
            $value = $canonical['value'] ?? CanonicalMode::Auto;

            if ($value === true) {
                $value = CanonicalMode::Auto;
            }

            if ($value === false) {
                $value = CanonicalMode::None;
            }

            if ($value instanceof CanonicalMode || is_string($value)) {
                $this->canonical(
                    $value,
                    forceHttps: $this->bool($canonical['forceHttps'] ?? $canonical['force_https'] ?? null),
                    trailingSlash: $this->bool($canonical['trailingSlash'] ?? $canonical['trailing_slash'] ?? null),
                );
            }

            return;
        }

        if ($canonical instanceof CanonicalMode || is_string($canonical)) {
            $this->canonical($canonical);

            return;
        }

        if ($canonical === true || is_null($canonical)) {
            $this->canonical(CanonicalMode::Auto);
        }

        if ($canonical === false) {
            $this->canonical(CanonicalMode::None);
        }
    }

    /**
     * @param  array<mixed, mixed>  $values
     */
    protected function fillOpenGraph(array $values): void
    {
        $this->og(
            type: $this->ogType($values['type'] ?? null),
            title: $this->string($values['title'] ?? null),
            description: $this->string($values['description'] ?? null),
            url: $this->string($values['url'] ?? null),
            image: $this->string($values['image'] ?? null),
            video: $this->string($values['video'] ?? null),
            audio: $this->string($values['audio'] ?? null),
            siteName: $this->string($values['siteName'] ?? $values['site_name'] ?? null),
            locale: $this->string($values['locale'] ?? null),
            determiner: $this->string($values['determiner'] ?? null),
        );
    }

    protected function fillOpenGraphImages(mixed $images): void
    {
        foreach ($this->items($images) as $image) {
            if (is_string($image)) {
                $this->ogImage($image);
            }

            if (is_array($image) && is_string($image['url'] ?? null)) {
                $this->ogImage(
                    $image['url'],
                    alt: $this->string($image['alt'] ?? null),
                    width: $this->integer($image['width'] ?? null),
                    height: $this->integer($image['height'] ?? null),
                    type: $this->string($image['type'] ?? null),
                    secureUrl: $this->string($image['secureUrl'] ?? $image['secure_url'] ?? null),
                );
            }
        }
    }

    protected function fillOpenGraphVideos(mixed $videos): void
    {
        foreach ($this->items($videos) as $video) {
            if (is_string($video)) {
                $this->ogVideo($video);
            }

            if (is_array($video) && is_string($video['url'] ?? null)) {
                $this->ogVideo(
                    $video['url'],
                    alt: $this->string($video['alt'] ?? null),
                    width: $this->integer($video['width'] ?? null),
                    height: $this->integer($video['height'] ?? null),
                    type: $this->string($video['type'] ?? null),
                    secureUrl: $this->string($video['secureUrl'] ?? $video['secure_url'] ?? null),
                );
            }
        }
    }

    protected function fillOpenGraphAudios(mixed $audios): void
    {
        foreach ($this->items($audios) as $audio) {
            if (is_string($audio)) {
                $this->ogAudio($audio);
            }

            if (is_array($audio) && is_string($audio['url'] ?? null)) {
                $this->ogAudio(
                    $audio['url'],
                    type: $this->string($audio['type'] ?? null),
                    secureUrl: $this->string($audio['secureUrl'] ?? $audio['secure_url'] ?? null),
                );
            }
        }
    }

    /**
     * @param  array<mixed, mixed>  $values
     */
    protected function fillTwitter(array $values): void
    {
        $this->twitter(
            card: $this->twitterCard($values['card'] ?? null),
            site: $this->string($values['site'] ?? null),
            creator: $this->string($values['creator'] ?? null),
            title: $this->string($values['title'] ?? null),
            description: $this->string($values['description'] ?? null),
            image: $this->string($values['image'] ?? null),
        );
    }

    protected function fillTwitterImage(mixed $image): void
    {
        if (is_string($image)) {
            $this->twitterImage($image);

            return;
        }

        if (is_array($image) && is_string($image['url'] ?? null)) {
            $this->twitterImage($image['url'], alt: $this->string($image['alt'] ?? null));
        }
    }

    /**
     * @param  array<mixed, mixed>  $preloads
     */
    protected function fillPreloads(array $preloads): void
    {
        foreach ($preloads as $href => $attributes) {
            if (is_string($attributes)) {
                $this->preload($attributes);
            }

            if (is_array($attributes)) {
                $this->preload(
                    $this->string($attributes['href'] ?? null) ?? $this->string($href) ?? '',
                    as: $this->string($attributes['as'] ?? null),
                    crossorigin: $this->boolOrString($attributes['crossorigin'] ?? null),
                    type: $this->string($attributes['type'] ?? null),
                    media: $this->string($attributes['media'] ?? null),
                );
            }
        }
    }

    /**
     * @param  array<mixed, mixed>  $prefetches
     */
    protected function fillPrefetches(array $prefetches): void
    {
        foreach ($prefetches as $href => $attributes) {
            if (is_string($attributes)) {
                $this->prefetch($attributes);
            }

            if (is_array($attributes)) {
                $this->prefetch(
                    $this->string($attributes['href'] ?? null) ?? $this->string($href) ?? '',
                    as: $this->string($attributes['as'] ?? null),
                );
            }
        }
    }

    /**
     * @param  array<mixed, mixed>  $preconnects
     */
    protected function fillPreconnects(array $preconnects): void
    {
        foreach ($preconnects as $href => $attributes) {
            if (is_string($attributes)) {
                $this->preconnect($attributes);
            }

            if (is_array($attributes)) {
                $this->preconnect(
                    $this->string($attributes['href'] ?? null) ?? $this->string($href) ?? '',
                    crossorigin: $this->boolOrString($attributes['crossorigin'] ?? null),
                );
            }
        }
    }

    /**
     * @param  array<mixed>  $dnsPrefetches
     */
    protected function fillDnsPrefetches(array $dnsPrefetches): void
    {
        foreach ($dnsPrefetches as $href) {
            if (is_string($href)) {
                $this->dnsPrefetch($href);
            }
        }
    }

    /**
     * @param  array<mixed, mixed>  $alternates
     */
    protected function fillAlternates(array $alternates): void
    {
        foreach ($alternates as $locale => $href) {
            if (is_string($href)) {
                $this->alternates[(string) $locale] = $href;
            }
        }
    }

    /**
     * @param  array<mixed, mixed>  $feeds
     */
    protected function fillFeeds(array $feeds): void
    {
        foreach ($feeds as $href => $feed) {
            if (is_string($href) && is_string($feed)) {
                $this->feed($href, $feed);
            }

            if (is_array($feed) && is_string($feed['title'] ?? null)) {
                $this->feed(
                    $this->string($feed['href'] ?? null) ?? $this->string($href) ?? '',
                    $feed['title'],
                    $this->string($feed['type'] ?? null) ?? 'rss',
                );
            }
        }
    }

    /**
     * @param  array<mixed, mixed>  $values
     */
    protected function fillMeta(array $values): void
    {
        foreach ($values as $key => $value) {
            if (is_string($key) && is_string($value)) {
                $this->meta($key, $value);
            }

            if (is_array($value) && is_string($value['key'] ?? null) && is_string($value['content'] ?? null)) {
                $this->meta($value['key'], $value['content'], $this->bool($value['property'] ?? null));
            }
        }
    }

    /**
     * @param  array<mixed, mixed>  $values
     */
    protected function fillLinks(array $values): void
    {
        foreach ($this->items($values) as $value) {
            if (! is_array($value) || ! is_string($value['rel'] ?? null) || ! is_string($value['href'] ?? null)) {
                continue;
            }

            $attributes = $this->named($value);
            unset($attributes['rel'], $attributes['href']);

            $this->link($value['rel'], $value['href'], $this->attributes($attributes));
        }
    }

    /**
     * @param  SchemaObject|SchemaPayload|array<int, SchemaObject|SchemaPayload>  $schemas
     */
    protected function fillSchemas(SchemaObject|array $schemas): void
    {
        if ($schemas instanceof SchemaObject || ! array_is_list($schemas)) {
            $this->schema($schemas instanceof SchemaObject ? $schemas : $this->named($schemas));

            return;
        }

        foreach ($schemas as $schema) {
            if ($schema instanceof SchemaObject || is_array($schema)) {
                $this->schema($schema instanceof SchemaObject ? $schema : $this->named($schema));
            }
        }
    }

    /**
     * @param  array<string, string|null>  $properties
     */
    protected function setOpenGraph(array $properties): void
    {
        foreach ($properties as $property => $value) {
            if (! is_null($value)) {
                $this->openGraph[$property] = $value;
            }
        }
    }

    /**
     * @param  array<string, string|null>  $properties
     */
    protected function setTwitter(array $properties): void
    {
        foreach ($properties as $property => $value) {
            if (! is_null($value)) {
                $this->twitter[$property] = $value;
            }
        }
    }

    /**
     * @return MediaAttributes
     */
    protected function media(
        string $url,
        ?string $alt = null,
        ?int $width = null,
        ?int $height = null,
        ?string $type = null,
        ?string $secureUrl = null,
    ): array {
        return array_filter([
            'url' => $url,
            'alt' => $alt,
            'width' => $width,
            'height' => $height,
            'type' => $type,
            'secure_url' => $secureUrl,
        ], fn (mixed $value): bool => ! is_null($value));
    }

    protected function string(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }

    protected function bool(mixed $value): ?bool
    {
        return is_bool($value) ? $value : null;
    }

    protected function integer(mixed $value): ?int
    {
        return is_int($value) ? $value : null;
    }

    protected function boolOrString(mixed $value): bool|string|null
    {
        return is_bool($value) || is_string($value) ? $value : null;
    }

    protected function ogType(mixed $value): OgType|string|null
    {
        return $value instanceof OgType || is_string($value) ? $value : null;
    }

    protected function twitterCard(mixed $value): TwitterCard|string|null
    {
        return $value instanceof TwitterCard || is_string($value) ? $value : null;
    }

    /**
     * @return array<int, mixed>
     */
    protected function items(mixed $value): array
    {
        return is_array($value) && array_is_list($value) ? $value : [$value];
    }

    /**
     * @param  array<mixed, mixed>  $values
     * @return array<string, mixed>
     */
    protected function named(array $values): array
    {
        $named = [];

        foreach ($values as $key => $value) {
            if (is_string($key)) {
                $named[$key] = $value;
            }
        }

        return $named;
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, bool|float|int|string|null>
     */
    protected function attributes(array $values): array
    {
        return array_filter(
            $values,
            fn (mixed $value): bool => is_null($value) || is_bool($value) || is_float($value) || is_int($value) || is_string($value)
        );
    }
}
