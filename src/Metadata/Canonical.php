<?php

declare(strict_types=1);

namespace Laravel\Head\Metadata;

use Illuminate\Http\Request;
use Laravel\Head\Rendering\ResolvedHead;
use Laravel\Head\Rendering\TagRenderer;

/**
 * @phpstan-consistent-constructor
 */
class Canonical extends Section
{
    public function __construct(
        protected string $mode,
        protected ?string $url = null,
        protected ?bool $forceHttps = null,
        protected ?bool $trailingSlash = null,
    ) {}

    public static function key(): string
    {
        return 'canonical';
    }

    public static function make(string|false|null $url = null, ?bool $forceHttps = null, ?bool $trailingSlash = null): self
    {
        return $url === false
            ? new self('none')
            : new self(is_null($url) ? 'auto' : 'url', $url, $forceHttps, $trailingSlash);
    }

    public static function fromRouteAttribute(string $key, mixed $value): ?self
    {
        if ($key !== 'canonical') {
            return null;
        }

        $canonical = $value;

        if (is_array($canonical)) {
            if (self::bool($canonical['none'] ?? null) === true) {
                return self::make(false);
            }

            $value = $canonical['value'] ?? null;

            if ($value === false) {
                return self::make(false);
            }

            if ($value === true || self::bool($canonical['auto'] ?? null) === true) {
                $value = null;
            }

            return is_string($value) || is_null($value)
                ? self::make(
                    $value,
                    forceHttps: self::bool($canonical['forceHttps'] ?? null),
                    trailingSlash: self::bool($canonical['trailingSlash'] ?? null),
                )
                : null;
        }

        if (is_string($canonical)) {
            return self::make($canonical);
        }

        if ($canonical === true || is_null($canonical)) {
            return self::make();
        }

        if ($canonical === false) {
            return self::make(false);
        }

        return null;
    }

    public function overlayOn(?Section $base): static
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
     * Canonical sections always carry a resolution mode, so they are never empty.
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
