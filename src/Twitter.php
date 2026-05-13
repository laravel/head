<?php

declare(strict_types=1);

namespace Laravel\Head;

class Twitter
{
    public function __construct(protected HeadData $head) {}

    public function card(string $card): static
    {
        return $this->set('card', $card);
    }

    public function site(string $site): static
    {
        return $this->set('site', $site);
    }

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

    public function set(string $name, string $value): static
    {
        $this->head->twitter[$name] = $value;

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
