<?php

declare(strict_types=1);

namespace Laravel\Head\Rendering;

use InvalidArgumentException;

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

        return $this->element('title', e($title), $this->inertiaAttributes($inertiaKey));
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

        return $this->voidElement(
            'meta',
            $this->inertiaAttributes($inertiaKey),
            [$attribute => $key],
            $attributes,
        );
    }

    public function link(string $rel, string $href, ?string $inertiaKey = null): string
    {
        $inertiaKey ??= $this->stableKey($rel, $href);

        return $this->voidElement(
            'link',
            $this->inertiaAttributes($inertiaKey),
            ['rel' => $rel, 'href' => $href],
        );
    }

    /**
     * @param  array<string, bool|float|int|string|null>  $attributes
     */
    public function linkWithAttributes(string $rel, array $attributes, ?string $inertiaKey = null): string
    {
        $inertiaKey ??= $this->stableKey($rel, (string) ($attributes['href'] ?? serialize($attributes)));

        return $this->voidElement(
            'link',
            $this->inertiaAttributes($inertiaKey),
            ['rel' => $rel],
            $attributes,
        );
    }

    /**
     * @param  array<string, mixed>  $schema
     */
    public function jsonLd(array $schema, ?string $inertiaKey = null): string
    {
        $inertiaKey ??= $this->stableKey('schema', json_encode($schema, JSON_THROW_ON_ERROR));

        return $this->element(
            'script',
            json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR),
            $this->inertiaAttributes($inertiaKey),
            ['type' => 'application/ld+json'],
        );
    }

    /**
     * @param  array<string, bool|float|int|string|null>  $attributes
     */
    public function attributes(array $attributes): string
    {
        return $this->renderAttributes($attributes);
    }

    /**
     * Attribute names aren't HTML-escaped, so reject anything that could
     * break out of the attribute position. The "u" modifier also makes
     * invalid UTF-8 fail the match.
     */
    protected function isValidAttributeName(string $name): bool
    {
        return preg_match('/^[^\x00-\x20\x7F-\x9F"\'\/=>]+$/u', $name) === 1;
    }

    /**
     * Generate a stable, content-derived data-inertia key for a tag.
     */
    public function stableKey(string $prefix, string $value): string
    {
        return $prefix.':'.substr(md5($value), 0, 16);
    }

    /**
     * @param  array<int|string, bool|float|int|string|null>  ...$attributeSets
     */
    protected function renderAttributes(array ...$attributeSets): string
    {
        $rendered = [];
        $seen = [];

        foreach ($attributeSets as $attributes) {
            foreach ($attributes as $name => $value) {
                if ($value === false || is_null($value)) {
                    continue;
                }

                $name = (string) $name;

                if (! $this->isValidAttributeName($name)) {
                    throw new InvalidArgumentException('Invalid HTML attribute name.');
                }

                $normalizedName = strtolower($name);

                if (isset($seen[$normalizedName])) {
                    throw new InvalidArgumentException("Duplicate HTML attribute name [{$name}].");
                }

                $seen[$normalizedName] = true;
                $rendered[] = $value === true
                    ? $name
                    : $name.'="'.e((string) $value).'"';
            }
        }

        return implode(' ', $rendered);
    }

    /**
     * @param  array<int|string, bool|float|int|string|null>  ...$attributeSets
     */
    protected function voidElement(string $name, array ...$attributeSets): string
    {
        return '<'.$name.$this->renderedAttributes(...$attributeSets).'>';
    }

    /**
     * @param  array<int|string, bool|float|int|string|null>  ...$attributeSets
     */
    protected function element(string $name, string $content, array ...$attributeSets): string
    {
        return '<'.$name.$this->renderedAttributes(...$attributeSets).'>'.$content.'</'.$name.'>';
    }

    /**
     * @param  array<int|string, bool|float|int|string|null>  ...$attributeSets
     */
    protected function renderedAttributes(array ...$attributeSets): string
    {
        $attributes = $this->renderAttributes(...$attributeSets);

        return $attributes === '' ? '' : ' '.$attributes;
    }

    /**
     * @return array<string, string>
     */
    protected function inertiaAttributes(?string $key): array
    {
        return $this->withInertiaAttributes && ! is_null($key)
            ? ['data-inertia' => $key]
            : [];
    }
}
