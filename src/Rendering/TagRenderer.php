<?php

declare(strict_types=1);

namespace Laravel\Head\Rendering;

class TagRenderer
{
    public function __construct(protected bool $withInertiaAttributes = false)
    {
        //
    }

    /**
     * Get a renderer that stamps tags with data-inertia ownership attributes.
     */
    public function withInertiaAttributes(): self
    {
        return new self(true);
    }

    public function title(string $title, ?string $inertiaKey = null): string
    {
        $inertiaKey ??= 'title';

        return '<title'.$this->inertiaAttribute($inertiaKey).'>'.e($title).'</title>';
    }

    /**
     * Render a meta tag keyed by the given attribute ("name" or "property").
     */
    public function meta(string $attribute, string $key, string|int|float|bool $content, ?string $inertiaKey = null): string
    {
        $inertiaKey ??= $key;

        return $this->metaWithAttributes($attribute, $key, ['content' => (string) $content], $inertiaKey);
    }

    /**
     * Render a meta tag keyed by the given attribute ("name" or "property") with arbitrary attributes.
     *
     * @param  array<string, bool|float|int|string|null>  $attributes
     */
    public function metaWithAttributes(string $attribute, string $key, array $attributes, ?string $inertiaKey = null): string
    {
        $inertiaKey ??= $key;

        return '<meta'.$this->inertiaAttribute($inertiaKey).' '.$attribute.'="'.e($key).'" '.$this->attributes($attributes).'>';
    }

    public function link(string $rel, string $href, ?string $inertiaKey = null): string
    {
        $inertiaKey ??= $this->stableKey($rel, $href);

        return '<link'.$this->inertiaAttribute($inertiaKey).' rel="'.e($rel).'" href="'.e($href).'">';
    }

    /**
     * @param  array<string, bool|float|int|string|null>  $attributes
     */
    public function linkWithAttributes(string $rel, array $attributes, ?string $inertiaKey = null): string
    {
        $inertiaKey ??= $this->stableKey($rel, (string) ($attributes['href'] ?? serialize($attributes)));

        return '<link'.$this->inertiaAttribute($inertiaKey).' rel="'.e($rel).'" '.$this->attributes($attributes).'>';
    }

    /**
     * @param  array<string, mixed>  $schema
     */
    public function jsonLd(array $schema, ?string $inertiaKey = null): string
    {
        $inertiaKey ??= $this->stableKey('schema', json_encode($schema, JSON_THROW_ON_ERROR));

        return '<script'.$this->inertiaAttribute($inertiaKey).' type="application/ld+json">'.json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR).'</script>';
    }

    /**
     * @param  array<string, bool|float|int|string|null>  $attributes
     */
    public function attributes(array $attributes): string
    {
        return collect($attributes)
            ->map(function (mixed $value, string $name): string {
                if (! $this->isValidAttributeName($name)) {
                    return '';
                }

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

    /**
     * Names aren't HTML-escaped, so reject anything that could break out
     * and inject another attribute (e.g. a space or "=").
     */
    protected function isValidAttributeName(string $name): bool
    {
        return preg_match('/^[a-z_:][-\w:.]*$/i', $name) === 1;
    }

    /**
     * Generate a stable, content-derived data-inertia key for a tag.
     */
    public function stableKey(string $prefix, string $value): string
    {
        return $prefix.':'.substr(md5($value), 0, 16);
    }

    protected function inertiaAttribute(?string $key): string
    {
        return $this->withInertiaAttributes && ! is_null($key) ? ' data-inertia="'.e($key).'"' : '';
    }
}
