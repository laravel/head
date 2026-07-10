<?php

declare(strict_types=1);

namespace Laravel\Head\Tags;

use Laravel\Head\Rendering\ResolvedHead;

/**
 * A tag builder holding a single string value.
 *
 * @phpstan-consistent-constructor
 */
abstract class StringTagBuilder extends TagBuilder
{
    public function __construct(protected ?string $value = null)
    {
        //
    }

    public static function make(string $value): static
    {
        return new static($value);
    }

    public static function fromRouteAttribute(string $key, mixed $value): ?self
    {
        return $key === static::key() && is_string($value)
            ? static::make($value)
            : null;
    }

    public function overlayOn(?TagBuilder $base): static
    {
        if (! $base instanceof self) {
            return $this;
        }

        return new static($this->value ?? $base->value);
    }

    public function isEmpty(): bool
    {
        return is_null($this->value);
    }

    public function render(): ?string
    {
        return $this->value;
    }

    public function toHeadArray(ResolvedHead $head): ?string
    {
        return $this->render();
    }
}
