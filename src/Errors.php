<?php

declare(strict_types=1);

namespace Laravel\Head;

use Laravel\Head\Support\HeadAttributes;
use Laravel\Head\Support\HeadData;

class Errors
{
    protected HeadData $defaults;

    /** @var array<int, HeadData> */
    protected array $statuses = [];

    public function __construct()
    {
        $this->defaults = new HeadData;
    }

    public function defaults(mixed ...$head): static
    {
        HeadAttributes::apply($this->defaults, HeadAttributes::arguments($head));

        return $this;
    }

    public function status(int $status, mixed ...$head): static
    {
        $data = new HeadData;

        HeadAttributes::apply($data, HeadAttributes::arguments($head));

        $this->statuses[$status] = $data;

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
