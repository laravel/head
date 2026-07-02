<?php

declare(strict_types=1);

namespace Laravel\Head\Tags;

use Illuminate\Http\Request;
use Laravel\Head\Rendering\ResolvedHead;
use Laravel\Head\Rendering\TagRenderer;

/**
 * @phpstan-consistent-constructor
 */
class Canonical extends TagBuilder
{
    public function __construct(
        protected string $mode,
        protected ?string $url = null,
        protected ?bool $forceHttps = null,
        protected ?bool $trailingSlash = null,
    ) {
        //
    }

    public static function key(): string
    {
        return 'canonical';
    }

    public static function make(?string $url = null, ?bool $forceHttps = null, ?bool $trailingSlash = null): self
    {
        return new self(is_null($url) ? 'auto' : 'url', $url, $forceHttps, $trailingSlash);
    }

    public static function fromRouteAttribute(string $key, mixed $value): ?self
    {
        if ($key !== 'canonical') {
            return null;
        }

        return match (true) {
            is_array($value) => self::fromRouteAttributeArray($value),
            is_string($value) => self::make($value),
            $value === true || is_null($value) => self::make(),
            default => null,
        };
    }

    public function overlayOn(?TagBuilder $base): static
    {
        if (! $base instanceof static) {
            return $this;
        }

        return new static(
            $this->mode,
            $this->url ?? $base->url,
            $this->forceHttps ?? $base->forceHttps,
            $this->trailingSlash ?? $base->trailingSlash,
        );
    }

    /**
     * Canonical builders always carry a resolution mode, so they are never empty.
     */
    public function isEmpty(): bool
    {
        return false;
    }

    public function render(?Request $request): ?string
    {
        $url = match ($this->mode) {
            'auto' => $request?->url(),
            'url' => $this->url,
            default => null,
        };

        if (is_null($url)) {
            return null;
        }

        return $this->normalizeUrl($url, $request, $this->forceHttps ?? true, $this->trailingSlash ?? false);
    }

    public function toHeadArray(ResolvedHead $head): ?string
    {
        return $this->render($head->request());
    }

    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return ($canonical = $this->render($head->request())) ? [$tags->link('canonical', $canonical, 'canonical')] : [];
    }

    /**
     * @param  array<mixed, mixed>  $attributes
     */
    private static function fromRouteAttributeArray(array $attributes): ?self
    {
        if (array_key_exists('none', $attributes) || ($attributes['value'] ?? null) === false) {
            return null;
        }

        $value = self::routeAttributeUrl($attributes);

        return is_string($value) || is_null($value)
            ? self::make(
                $value,
                forceHttps: self::bool($attributes['forceHttps'] ?? null),
                trailingSlash: self::bool($attributes['trailingSlash'] ?? null),
            )
            : null;
    }

    /**
     * @param  array<mixed, mixed>  $attributes
     */
    private static function routeAttributeUrl(array $attributes): mixed
    {
        $value = $attributes['value'] ?? null;

        return $value === true || self::bool($attributes['auto'] ?? null) === true
            ? null
            : $value;
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
}
