<?php

declare(strict_types=1);

namespace Laravel\Head;

use Closure;
use Laravel\Head\Routing\RouteAttributeParser;

class ErrorPages
{
    protected HeadData $defaults;

    /** @var array<int, HeadData> */
    protected array $statuses = [];

    /**
     * @param  Closure(callable(HeadBuilder): mixed): HeadData  $define
     */
    public function __construct(
        protected TagRegistry $registry,
        protected Closure $define,
    ) {
        $this->defaults = new HeadData;
    }

    /**
     * Define head metadata for every error page, using either named
     * route-attribute values or a single head builder callback.
     */
    public function defaults(mixed ...$head): static
    {
        $this->defaults = $this->apply($this->defaults, $head);

        return $this;
    }

    /**
     * Define head metadata for a specific error status, using either named
     * route-attribute values or a single head builder callback.
     */
    public function status(int $status, mixed ...$head): static
    {
        $this->statuses[$status] = $this->apply(new HeadData, $head);

        return $this;
    }

    public function forStatus(int $status): ?HeadData
    {
        if (! isset($this->statuses[$status]) && $this->defaults->isEmpty()) {
            return null;
        }

        return $this->defaults->merge($this->statuses[$status] ?? new HeadData);
    }

    /**
     * @param  array<int|string, mixed>  $head
     */
    protected function apply(HeadData $base, array $head): HeadData
    {
        if (array_keys($head) === [0] && $head[0] instanceof Closure) {
            return $base->merge(($this->define)($head[0]));
        }

        return RouteAttributeParser::apply($base, RouteAttributeParser::arguments($head), $this->registry);
    }
}
