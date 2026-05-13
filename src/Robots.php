<?php

declare(strict_types=1);

namespace Laravel\Head;

class Robots
{
    public function __construct(protected HeadData $head)
    {
        $this->head->robots ??= [];
    }

    public function index(): static
    {
        return $this->directive('index', without: 'noindex');
    }

    public function noindex(): static
    {
        return $this->directive('noindex', without: 'index');
    }

    public function follow(): static
    {
        return $this->directive('follow', without: 'nofollow');
    }

    public function nofollow(): static
    {
        return $this->directive('nofollow', without: 'follow');
    }

    /**
     * @param  array<int, string>|string  $directives
     */
    public function directives(array|string $directives): static
    {
        $this->head->robots = is_string($directives)
            ? array_map(trim(...), explode(',', $directives))
            : array_values($directives);

        return $this;
    }

    public function head(): HeadData
    {
        return $this->head;
    }

    protected function directive(string $directive, string $without): static
    {
        $this->head->robots = array_values(array_diff($this->head->robots ?? [], [$without]));

        if (! in_array($directive, $this->head->robots, true)) {
            $this->head->robots[] = $directive;
        }

        return $this;
    }

    /**
     * @param  array<int, mixed>  $parameters
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->head->{$method}(...$parameters);
    }
}
