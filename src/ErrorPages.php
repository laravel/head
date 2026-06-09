<?php

declare(strict_types=1);

namespace Laravel\Head;

use Laravel\Head\Routing\AttributeParser;

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
        $this->defaults = AttributeParser::apply($this->defaults, AttributeParser::arguments($head), $this->registry);

        return $this;
    }

    public function status(int $status, mixed ...$head): static
    {
        $this->statuses[$status] = AttributeParser::apply(new HeadData, AttributeParser::arguments($head), $this->registry);

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
