<?php

declare(strict_types=1);

namespace Laravel\Head;

class Canonical
{
    public function __construct(protected HeadData $head) {}

    public function auto(): static
    {
        $this->head->canonicalMode = 'auto';
        $this->head->canonicalUrl = null;

        return $this;
    }

    public function url(string $url): static
    {
        $this->head->canonicalMode = 'url';
        $this->head->canonicalUrl = $url;

        return $this;
    }

    public function none(): static
    {
        $this->head->canonicalMode = 'none';

        return $this;
    }

    public function forceHttps(bool $force = true): static
    {
        $this->head->canonicalForceHttps = $force;

        return $this;
    }

    public function preserveScheme(): static
    {
        return $this->forceHttps(false);
    }

    public function trailingSlash(bool $trailingSlash = true): static
    {
        $this->head->canonicalTrailingSlash = $trailingSlash;

        return $this;
    }

    public function withoutTrailingSlash(): static
    {
        return $this->trailingSlash(false);
    }

    public function head(): HeadData
    {
        return $this->head;
    }

    /**
     * @param  array<int, mixed>  $parameters
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->head->{$method}(...$parameters);
    }
}
