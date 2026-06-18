<?php

declare(strict_types=1);

namespace Laravel\Head;

use Laravel\Head\Routing\RouteAttributeParser;

class ErrorPages
{
    protected HeadData $defaults;

    /** @var array<int, HeadData> */
    protected array $statuses = [];

    public function __construct(protected MetadataRegistry $registry)
    {
        $this->defaults = new HeadData;
    }

    public function defaults(mixed ...$head): static
    {
        $this->defaults = RouteAttributeParser::apply($this->defaults, RouteAttributeParser::arguments($head), $this->registry);

        return $this;
    }

    public function status(int $status, mixed ...$head): static
    {
        $this->statuses[$status] = RouteAttributeParser::apply(new HeadData, RouteAttributeParser::arguments($head), $this->registry);

        return $this;
    }

    public function forStatus(int $status): ?HeadData
    {
        if (! isset($this->statuses[$status]) && $this->defaults->isEmpty()) {
            return null;
        }

        return $this->defaults->merge($this->statuses[$status] ?? new HeadData);
    }
}
