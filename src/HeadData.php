<?php

declare(strict_types=1);

namespace Laravel\Head;

use Illuminate\Contracts\Pagination\Paginator;
use Laravel\Head\Schema\SchemaObject;

/**
 * @phpstan-type LinkAttributes array{href: string, as?: string|null, crossorigin?: bool|string|null, type?: string|null, media?: string|null}
 * @phpstan-type FeedAttributes array{href: string, title: string, type: string}
 * @phpstan-type SchemaPayload array<string, mixed>
 */
class HeadData
{
    public ?string $title = null;

    public ?string $titleFallback = null;

    public ?string $titlePrefix = null;

    public ?string $titleSuffix = null;

    public ?bool $titleBare = null;

    public ?string $description = null;

    public ?string $canonicalUrl = null;

    public ?string $canonicalMode = null;

    public ?bool $canonicalForceHttps = null;

    public ?bool $canonicalTrailingSlash = null;

    /** @var array<int, string>|null */
    public ?array $robots = null;

    /** @var array<string, string> */
    public array $openGraph = [];

    /** @var array<string, string> */
    public array $twitter = [];

    /** @var array<string, LinkAttributes> */
    public array $preloads = [];

    /** @var array<string, LinkAttributes> */
    public array $prefetches = [];

    /** @var array<string, LinkAttributes> */
    public array $preconnects = [];

    /** @var array<string, array{href: string}> */
    public array $dnsPrefetches = [];

    /** @var array<string, string> */
    public array $links = [];

    /** @var array<string, string> */
    public array $alternates = [];

    /** @var array<string, FeedAttributes> */
    public array $feeds = [];

    /** @var array<string, SchemaObject|SchemaPayload> */
    public array $schemas = [];

    public static function base(): self
    {
        $head = new self;

        $head->canonicalMode = 'auto';
        $head->canonicalForceHttps = true;
        $head->canonicalTrailingSlash = false;
        $head->robots = ['index', 'follow'];

        return $head;
    }

    public function merge(HeadData $data): self
    {
        $merged = clone $this;

        $merged->title = $data->title ?? $merged->title;
        $merged->titleFallback = $data->titleFallback ?? $merged->titleFallback;
        $merged->titlePrefix = $data->titlePrefix ?? $merged->titlePrefix;
        $merged->titleSuffix = $data->titleSuffix ?? $merged->titleSuffix;
        $merged->titleBare = $data->titleBare ?? $merged->titleBare;
        $merged->description = $data->description ?? $merged->description;
        $merged->canonicalUrl = $data->canonicalUrl ?? $merged->canonicalUrl;
        $merged->canonicalMode = $data->canonicalMode ?? $merged->canonicalMode;
        $merged->canonicalForceHttps = $data->canonicalForceHttps ?? $merged->canonicalForceHttps;
        $merged->canonicalTrailingSlash = $data->canonicalTrailingSlash ?? $merged->canonicalTrailingSlash;
        $merged->robots = $data->robots ?? $merged->robots;

        $merged->openGraph = array_replace($merged->openGraph, $data->openGraph);
        $merged->twitter = array_replace($merged->twitter, $data->twitter);
        $merged->preloads = array_replace($merged->preloads, $data->preloads);
        $merged->prefetches = array_replace($merged->prefetches, $data->prefetches);
        $merged->preconnects = array_replace($merged->preconnects, $data->preconnects);
        $merged->dnsPrefetches = array_replace($merged->dnsPrefetches, $data->dnsPrefetches);
        $merged->links = array_replace($merged->links, $data->links);
        $merged->alternates = array_replace($merged->alternates, $data->alternates);
        $merged->feeds = array_replace($merged->feeds, $data->feeds);
        $merged->schemas = array_replace($merged->schemas, $data->schemas);

        return $merged;
    }

    public function isEmpty(): bool
    {
        return is_null($this->title)
            && is_null($this->titleFallback)
            && is_null($this->titlePrefix)
            && is_null($this->titleSuffix)
            && is_null($this->titleBare)
            && is_null($this->description)
            && is_null($this->canonicalUrl)
            && is_null($this->canonicalMode)
            && is_null($this->canonicalForceHttps)
            && is_null($this->canonicalTrailingSlash)
            && is_null($this->robots)
            && $this->openGraph === []
            && $this->twitter === []
            && $this->preloads === []
            && $this->prefetches === []
            && $this->preconnects === []
            && $this->dnsPrefetches === []
            && $this->links === []
            && $this->alternates === []
            && $this->feeds === []
            && $this->schemas === [];
    }

    public function title(?string $title = null): static|Title
    {
        if (is_null($title)) {
            return new Title($this);
        }

        $this->title = $title;

        return $this;
    }

    public function description(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function canonical(?string $url = null): static|Canonical
    {
        if (is_null($url)) {
            return new Canonical($this);
        }

        $this->canonicalMode = 'url';
        $this->canonicalUrl = $url;

        return $this;
    }

    public function og(): OpenGraph
    {
        return new OpenGraph($this);
    }

    public function twitter(): Twitter
    {
        return new Twitter($this);
    }

    public function robots(): Robots
    {
        return new Robots($this);
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
            $this->links['prev'] = $previous;
        }

        if ($next = $paginator->nextPageUrl()) {
            $this->links['next'] = $next;
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
        if (array_key_exists('title', $values) && is_string($values['title'])) {
            $this->title($values['title']);
        }

        if (array_key_exists('description', $values) && is_string($values['description'])) {
            $this->description($values['description']);
        }

        if (array_key_exists('canonical', $values) && (is_string($values['canonical']) || is_bool($values['canonical']) || is_null($values['canonical']))) {
            $this->fillCanonical($values['canonical']);
        }

        if (array_key_exists('robots', $values) && (is_array($values['robots']) || is_string($values['robots']))) {
            $this->fillRobots($values['robots']);
        }

        if (array_key_exists('og', $values) && is_array($values['og'])) {
            $this->fillOpenGraph($values['og']);
        }

        if (array_key_exists('openGraph', $values) && is_array($values['openGraph'])) {
            $this->fillOpenGraph($values['openGraph']);
        }

        if (array_key_exists('twitter', $values) && is_array($values['twitter'])) {
            $this->fillTwitter($values['twitter']);
        }

        if (array_key_exists('preload', $values) && is_array($values['preload'])) {
            $this->fillPreloads($values['preload']);
        }

        if (array_key_exists('preloads', $values) && is_array($values['preloads'])) {
            $this->fillPreloads($values['preloads']);
        }

        if (array_key_exists('prefetch', $values) && is_array($values['prefetch'])) {
            $this->fillPrefetches($values['prefetch']);
        }

        if (array_key_exists('prefetches', $values) && is_array($values['prefetches'])) {
            $this->fillPrefetches($values['prefetches']);
        }

        if (array_key_exists('preconnect', $values) && is_array($values['preconnect'])) {
            $this->fillPreconnects($values['preconnect']);
        }

        if (array_key_exists('preconnects', $values) && is_array($values['preconnects'])) {
            $this->fillPreconnects($values['preconnects']);
        }

        if (array_key_exists('dnsPrefetch', $values) && is_array($values['dnsPrefetch'])) {
            $this->fillDnsPrefetches($values['dnsPrefetch']);
        }

        if (array_key_exists('dnsPrefetches', $values) && is_array($values['dnsPrefetches'])) {
            $this->fillDnsPrefetches($values['dnsPrefetches']);
        }

        if (array_key_exists('alternates', $values) && is_array($values['alternates'])) {
            $this->fillAlternates($values['alternates']);
        }

        if (array_key_exists('feed', $values) && is_array($values['feed'])) {
            $this->fillFeeds($values['feed']);
        }

        if (array_key_exists('feeds', $values) && is_array($values['feeds'])) {
            $this->fillFeeds($values['feeds']);
        }

        if (array_key_exists('schema', $values) && ($values['schema'] instanceof SchemaObject || is_array($values['schema']))) {
            $this->fillSchemas($values['schema']);
        }

        if (array_key_exists('schemas', $values) && ($values['schemas'] instanceof SchemaObject || is_array($values['schemas']))) {
            $this->fillSchemas($values['schemas']);
        }

        return $this;
    }

    protected function fillCanonical(string|bool|null $canonical): void
    {
        match ($canonical) {
            false => (new Canonical($this))->none(),
            true, null => (new Canonical($this))->auto(),
            default => $this->canonical($canonical),
        };
    }

    /**
     * @param  array<mixed>|string  $robots
     */
    protected function fillRobots(array|string $robots): void
    {
        if (is_string($robots)) {
            $this->robots = array_map(trim(...), explode(',', $robots));

            return;
        }

        $this->robots = array_values(array_filter($robots, is_string(...)));
    }

    /**
     * @param  array<mixed, mixed>  $values
     */
    protected function fillOpenGraph(array $values): void
    {
        foreach ($values as $key => $value) {
            if (is_string($key) && is_string($value)) {
                $this->openGraph[$key] = $value;
            }
        }
    }

    /**
     * @param  array<mixed, mixed>  $values
     */
    protected function fillTwitter(array $values): void
    {
        foreach ($values as $key => $value) {
            if (is_string($key) && is_string($value)) {
                $this->twitter[$key] = $value;
            }
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

    protected function string(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }

    protected function boolOrString(mixed $value): bool|string|null
    {
        return is_bool($value) || is_string($value) ? $value : null;
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
}
