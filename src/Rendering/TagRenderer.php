<?php

declare(strict_types=1);

namespace Laravel\Head\Rendering;

class TagRenderer
{
    public function title(string $title): string
    {
        return '<title>'.e($title).'</title>';
    }

    public function meta(string $attribute, string $key, string|int|float|bool $content): string
    {
        return '<meta '.$attribute.'="'.e($key).'" content="'.e((string) $content).'">';
    }

    public function link(string $rel, string $href): string
    {
        return '<link rel="'.e($rel).'" href="'.e($href).'">';
    }

    /**
     * @param  array<string, bool|float|int|string|null>  $attributes
     */
    public function linkWithAttributes(string $rel, array $attributes): string
    {
        return '<link rel="'.e($rel).'" '.$this->attributes($attributes).'>';
    }

    /**
     * @param  array<string, mixed>  $schema
     */
    public function jsonLd(array $schema): string
    {
        return '<script type="application/ld+json">'.json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR).'</script>';
    }

    /**
     * @param  array<string, bool|float|int|string|null>  $attributes
     */
    public function attributes(array $attributes): string
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
}
