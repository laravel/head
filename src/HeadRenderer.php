<?php

declare(strict_types=1);

namespace Laravel\Head;

use Illuminate\Http\Request;
use Laravel\Head\Schema\SchemaObject;

class HeadRenderer
{
    public function __construct(protected SchemaValidator $schemas) {}

    public function render(HeadData $head, ?Request $request = null): string
    {
        return collect($this->tags($head, $request))
            ->filter()
            ->implode(PHP_EOL);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(HeadData $head, ?Request $request = null): array
    {
        return [
            'title' => $this->title($head),
            'description' => $head->description,
            'canonical' => $this->canonical($head, $request),
            'robots' => $head->robots,
            'openGraph' => $this->openGraphPayload($head),
            'twitter' => $this->twitterPayload($head),
            'links' => [
                'performance' => [
                    'preload' => array_values($head->preloads),
                    'prefetch' => array_values($head->prefetches),
                    'preconnect' => array_values($head->preconnects),
                    'dnsPrefetch' => array_values($head->dnsPrefetches),
                ],
                'pagination' => $head->paginationLinks,
                'alternates' => $head->alternates,
                'feeds' => array_values($head->feeds),
                'generic' => array_values($head->genericLinks),
            ],
            'meta' => array_values($head->meta),
            'schemas' => array_map(
                fn (SchemaObject|array $schema): array => $this->schema($schema),
                array_values($head->schemas),
            ),
        ];
    }

    /**
     * @return array<int, string|null>
     */
    protected function tags(HeadData $head, ?Request $request): array
    {
        return [
            $this->titleTag($head),
            $head->description ? $this->meta('name', 'description', $head->description) : null,
            ($canonical = $this->canonical($head, $request)) ? $this->link('canonical', $canonical) : null,
            $head->robots ? $this->meta('name', 'robots', $head->robots) : null,
            ...$this->openGraphTags($head),
            ...$this->twitterTags($head),
            ...$this->paginationTags($head),
            ...$this->alternateTags($head),
            ...$this->feedTags($head),
            ...$this->performanceTags($head),
            ...$this->genericMetaTags($head),
            ...$this->genericLinkTags($head),
            ...$this->schemaTags($head),
        ];
    }

    protected function titleTag(HeadData $head): ?string
    {
        return ($title = $this->title($head)) ? '<title>'.e($title).'</title>' : null;
    }

    protected function title(HeadData $head): ?string
    {
        if (is_null($head->title)) {
            return null;
        }

        if ($head->titleDecorated === false) {
            return $head->title;
        }

        return ($head->titlePrefix ?? '').$head->title.($head->titleSuffix ?? '');
    }

    protected function canonical(HeadData $head, ?Request $request): ?string
    {
        if ($head->canonicalMode === 'none') {
            return null;
        }

        $url = $head->canonicalMode === 'url'
            ? $head->canonicalUrl
            : $request?->url();

        if (is_null($url)) {
            return null;
        }

        return $this->normalizeUrl($url, $request, $head->canonicalForceHttps ?? true, $head->canonicalTrailingSlash ?? false);
    }

    protected function normalizeUrl(string $url, ?Request $request, bool $forceHttps, bool $trailingSlash): string
    {
        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            $url = rtrim($request?->getSchemeAndHttpHost() ?? '', '/').'/'.ltrim($url, '/');
        }

        if ($forceHttps) {
            $url = preg_replace('/^http:\/\//', 'https://', $url) ?? $url;
        }

        if ($trailingSlash) {
            return rtrim($url, '/').'/';
        }

        $path = parse_url($url, PHP_URL_PATH);

        if ($path === null || $path === false || $path === '' || $path === '/') {
            return rtrim($url, '/').'/';
        }

        return rtrim($url, '/');
    }

    /**
     * @return array<string, string>
     */
    protected function openGraph(HeadData $head): array
    {
        $values = $head->openGraph;

        if (($title = $this->title($head)) && ! isset($values['title'])) {
            $values['title'] = $title;
        }

        if ($head->description && ! isset($values['description'])) {
            $values['description'] = $head->description;
        }

        return $values;
    }

    /**
     * @return array<string, mixed>
     */
    protected function openGraphPayload(HeadData $head): array
    {
        return array_filter([
            ...$this->openGraph($head),
            'images' => array_values($head->openGraphImages),
            'videos' => array_values($head->openGraphVideos),
            'audios' => array_values($head->openGraphAudios),
        ]);
    }

    /**
     * @return array<string, string>
     */
    protected function twitter(HeadData $head): array
    {
        $values = $head->twitter;

        if (($title = $this->title($head)) && ! isset($values['title'])) {
            $values['title'] = $title;
        }

        if ($head->description && ! isset($values['description'])) {
            $values['description'] = $head->description;
        }

        return $values;
    }

    /**
     * @return array<string, mixed>
     */
    protected function twitterPayload(HeadData $head): array
    {
        return array_filter([
            ...$this->twitter($head),
            'image' => $this->twitterImage($head),
        ]);
    }

    /**
     * @return array<int, string>
     */
    protected function openGraphTags(HeadData $head): array
    {
        $tags = array_map(
            fn (string $property, string $content): string => $this->meta('property', 'og:'.$property, $content),
            array_keys($this->openGraph($head)),
            $this->openGraph($head),
        );

        return [
            ...$tags,
            ...$this->openGraphMediaTags('image', $head->openGraphImages),
            ...$this->openGraphMediaTags('video', $head->openGraphVideos),
            ...$this->openGraphMediaTags('audio', $head->openGraphAudios),
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function twitterTags(HeadData $head): array
    {
        $tags = array_map(
            fn (string $name, string $content): string => $this->meta('name', 'twitter:'.$name, $content),
            array_keys($this->twitter($head)),
            $this->twitter($head),
        );

        if (is_null($image = $this->twitterImage($head))) {
            return $tags;
        }

        $tags[] = $this->meta('name', 'twitter:image', $image['url']);

        if (isset($image['alt'])) {
            $tags[] = $this->meta('name', 'twitter:image:alt', $image['alt']);
        }

        return $tags;
    }

    /**
     * @return array{url: string, alt?: string}|null
     */
    protected function twitterImage(HeadData $head): ?array
    {
        if (! is_null($head->twitterImage)) {
            $image = $head->twitterImage;
        } elseif ($head->openGraphImages !== []) {
            $image = reset($head->openGraphImages);
        } else {
            return null;
        }

        $twitterImage = ['url' => $image['url']];

        if (isset($image['alt'])) {
            $twitterImage['alt'] = $image['alt'];
        }

        return $twitterImage;
    }

    /**
     * @return array<int, string>
     */
    protected function paginationTags(HeadData $head): array
    {
        return array_map(
            fn (string $rel, string $href): string => '<link rel="'.e($rel).'" href="'.e($href).'">',
            array_keys($head->paginationLinks),
            $head->paginationLinks,
        );
    }

    /**
     * @return array<int, string>
     */
    protected function alternateTags(HeadData $head): array
    {
        return array_map(
            fn (string $hreflang, string $href): string => '<link rel="alternate" hreflang="'.e($hreflang).'" href="'.e($href).'">',
            array_keys($head->alternates),
            $head->alternates,
        );
    }

    /**
     * @return array<int, string>
     */
    protected function feedTags(HeadData $head): array
    {
        return array_map(function (array $feed): string {
            $type = $feed['type'] === 'atom' ? 'application/atom+xml' : 'application/rss+xml';

            return '<link rel="alternate" type="'.e($type).'" title="'.e($feed['title']).'" href="'.e($feed['href']).'">';
        }, array_values($head->feeds));
    }

    /**
     * @return array<int, string>
     */
    protected function performanceTags(HeadData $head): array
    {
        return [
            ...array_map(fn (array $attributes): string => $this->linkWithAttributes('preload', $attributes), array_values($head->preloads)),
            ...array_map(fn (array $attributes): string => $this->linkWithAttributes('prefetch', $attributes), array_values($head->prefetches)),
            ...array_map(fn (array $attributes): string => $this->linkWithAttributes('preconnect', $attributes), array_values($head->preconnects)),
            ...array_map(fn (array $attributes): string => $this->linkWithAttributes('dns-prefetch', $attributes), array_values($head->dnsPrefetches)),
        ];
    }

    /**
     * @param  array<string, array{url: string, alt?: string|null, width?: int|null, height?: int|null, type?: string|null, secure_url?: string|null}>  $media
     * @return array<int, string>
     */
    protected function openGraphMediaTags(string $property, array $media): array
    {
        $tags = [];

        foreach ($media as $attributes) {
            $tags[] = $this->meta('property', 'og:'.$property, $attributes['url']);

            foreach (['secure_url', 'type', 'width', 'height', 'alt'] as $attribute) {
                if (isset($attributes[$attribute])) {
                    $tags[] = $this->meta('property', 'og:'.$property.':'.$attribute, $attributes[$attribute]);
                }
            }
        }

        return $tags;
    }

    /**
     * @return array<int, string>
     */
    protected function genericMetaTags(HeadData $head): array
    {
        return array_map(function (array $meta): string {
            $attribute = ($meta['property'] ?? $this->isRdfaProperty($meta['key'])) ? 'property' : 'name';

            return $this->meta($attribute, $meta['key'], $meta['content']);
        }, array_values($head->meta));
    }

    /**
     * @return array<int, string>
     */
    protected function genericLinkTags(HeadData $head): array
    {
        return array_map(function (array $link): string {
            return $this->linkWithAttributes($link['rel'], ['href' => $link['href'], ...$link['attributes']]);
        }, array_values($head->genericLinks));
    }

    /**
     * @return array<int, string>
     */
    protected function schemaTags(HeadData $head): array
    {
        return array_map(function (SchemaObject|array $schema): string {
            return '<script type="application/ld+json">'.json_encode($this->schema($schema), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR).'</script>';
        }, array_values($head->schemas));
    }

    /**
     * @param  SchemaObject|array<string, mixed>  $schema
     * @return array<string, mixed>
     */
    protected function schema(SchemaObject|array $schema): array
    {
        $payload = $schema instanceof SchemaObject ? $schema->toJsonLd() : $schema;

        $this->schemas->validate($payload);

        return $payload;
    }

    protected function meta(string $attribute, string $key, string|int|float|bool $content): string
    {
        return '<meta '.$attribute.'="'.e($key).'" content="'.e((string) $content).'">';
    }

    protected function link(string $rel, string $href): string
    {
        return '<link rel="'.e($rel).'" href="'.e($href).'">';
    }

    /**
     * @param  array<string, bool|float|int|string|null>  $attributes
     */
    protected function linkWithAttributes(string $rel, array $attributes): string
    {
        return '<link rel="'.e($rel).'" '.$this->attributes($attributes).'>';
    }

    /**
     * @param  array<string, bool|float|int|string|null>  $attributes
     */
    protected function attributes(array $attributes): string
    {
        return collect($attributes)
            ->map(function (mixed $value, string $name): string {
                if ($value === true) {
                    return e($name);
                }

                if ($value === false || is_null($value)) {
                    return '';
                }

                return e($name).'="'.e((string) $value).'"';
            })
            ->filter()
            ->implode(' ');
    }

    protected function isRdfaProperty(string $key): bool
    {
        foreach (['og:', 'article:', 'book:', 'profile:', 'music:', 'video:', 'fb:', 'product:'] as $prefix) {
            if (str_starts_with($key, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
