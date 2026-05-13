<?php

declare(strict_types=1);

namespace Laravel\Head;

class Title
{
    public function __construct(protected HeadData $head) {}

    public function prefix(string $prefix): static
    {
        $this->head->titlePrefix = $prefix;

        return $this;
    }

    public function suffix(string $suffix): static
    {
        $this->head->titleSuffix = $suffix;

        return $this;
    }

    public function fallback(string $title): static
    {
        $this->head->titleFallback = $title;

        return $this;
    }

    public function bare(bool $bare = true): static
    {
        $this->head->titleBare = $bare;

        return $this;
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
