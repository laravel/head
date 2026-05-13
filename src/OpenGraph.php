<?php

declare(strict_types=1);

namespace Laravel\Head;

class OpenGraph
{
    public function __construct(protected HeadData $head) {}

    public function title(string $title): static
    {
        return $this->set('title', $title);
    }

    public function description(string $description): static
    {
        return $this->set('description', $description);
    }

    public function image(string $url): static
    {
        return $this->set('image', $url);
    }

    public function siteName(string $siteName): static
    {
        return $this->set('site_name', $siteName);
    }

    public function type(string $type): static
    {
        return $this->set('type', $type);
    }

    public function url(string $url): static
    {
        return $this->set('url', $url);
    }

    public function set(string $property, string $value): static
    {
        $this->head->openGraph[$property] = $value;

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
