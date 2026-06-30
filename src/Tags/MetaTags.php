<?php

declare(strict_types=1);

namespace Laravel\Head\Tags;

use Illuminate\Support\Str;
use Laravel\Head\Rendering\ResolvedHead;
use Laravel\Head\Rendering\TagRenderer;

/**
 * @phpstan-consistent-constructor
 *
 * @phpstan-type MetaAttributes array{key: string, content: string, property?: bool|null, media?: string|null}
 */
class MetaTags extends GroupedTagBuilder
{
    /**
     * @param  array<string, MetaAttributes>  $tags
     */
    public function __construct(protected array $tags = [])
    {
        //
    }

    public static function key(): string
    {
        return 'meta';
    }

    public static function fromRouteAttribute(string $key, mixed $value): ?self
    {
        if ($key === 'webAppCapable' && is_bool($value)) {
            return (new self)->tag(self::aliases()[$key], $value ? 'yes' : 'no');
        }

        if (array_key_exists($key, self::aliases()) && is_string($value)) {
            return (new self)->tag(self::aliases()[$key], $value);
        }

        if ($key !== 'meta' || ! is_array($value)) {
            return null;
        }

        $tags = new self;

        foreach ($value as $metaKey => $meta) {
            if (is_string($metaKey) && is_string($meta)) {
                $tags->tag($metaKey, $meta);
            }

            if (is_array($meta) && is_string($meta['key'] ?? null) && is_string($meta['content'] ?? null)) {
                $tags->tag(
                    $meta['key'],
                    $meta['content'],
                    property: self::bool($meta['property'] ?? null),
                    media: self::string($meta['media'] ?? null),
                );
            }
        }

        return $tags;
    }

    public static function routeAttributeKeys(): array
    {
        return ['meta', ...array_keys(self::aliases())];
    }

    public function tag(string $key, string $content, ?bool $property = null, ?string $media = null): static
    {
        $this->tags[$this->tagKey($key, $property, $media)] = array_filter([
            'key' => $key,
            'content' => $content,
            'property' => $property,
            'media' => $media,
        ], fn ($value) => ! is_null($value));

        return $this;
    }

    public function overlayOn(?TagBuilder $base): static
    {
        if (! $base instanceof self) {
            return $this;
        }

        return new static(array_replace($base->tags, $this->tags));
    }

    public function isEmpty(): bool
    {
        return $this->tags === [];
    }

    /**
     * @return array<int, MetaAttributes>
     */
    public function toHeadArray(ResolvedHead $head): array
    {
        return $this->headArray();
    }

    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return array_map(function (array $meta) use ($tags): string {
            $attribute = $this->resolveAttribute($meta['key'], $meta['property'] ?? null);

            return $tags->metaWithAttributes($attribute, $meta['key'], [
                'content' => $meta['content'],
                'media' => $meta['media'] ?? null,
            ], $this->inertiaKey($meta));
        }, $this->headArray());
    }

    /**
     * @return array<int, MetaAttributes>
     */
    protected function headArray(): array
    {
        return array_values($this->tags);
    }

    protected function isRdfaProperty(string $key): bool
    {
        return Str::startsWith($key, ['og:', 'article:', 'book:', 'profile:', 'music:', 'video:', 'fb:', 'product:']);
    }

    /**
     * Resolve the HTML attribute a meta tag is keyed under, honoring an explicit
     * property flag and falling back to RDFa namespace detection.
     */
    protected function resolveAttribute(string $key, ?bool $property): string
    {
        return ($property ?? $this->isRdfaProperty($key)) ? 'property' : 'name';
    }

    protected function tagKey(string $key, ?bool $property, ?string $media): string
    {
        return implode('|', [
            $this->resolveAttribute($key, $property),
            $key,
            $media ?? '',
        ]);
    }

    /**
     * @param  MetaAttributes  $meta
     */
    protected function inertiaKey(array $meta): string
    {
        if (! isset($meta['media'])) {
            return self::inertiaAliases()[$meta['key']] ?? $meta['key'];
        }

        return 'meta:'.substr(md5($this->tagKey($meta['key'], $meta['property'] ?? null, $meta['media'])), 0, 16);
    }

    /**
     * @return array<string, string>
     */
    protected static function aliases(): array
    {
        return [
            'themeColor' => 'theme-color',
            'applicationName' => 'application-name',
            'colorScheme' => 'color-scheme',
            'referrer' => 'referrer',
            'viewport' => 'viewport',
            'appleWebAppTitle' => 'apple-mobile-web-app-title',
            'webAppCapable' => 'mobile-web-app-capable',
            'appleWebAppStatusBarStyle' => 'apple-mobile-web-app-status-bar-style',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected static function inertiaAliases(): array
    {
        return array_flip(self::aliases());
    }
}
