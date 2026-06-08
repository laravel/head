<?php

declare(strict_types=1);

namespace Laravel\Head\Support;

abstract class PartialHeadValue
{
    /**
     * @return array<string, mixed>
     */
    abstract protected function parts(): array;

    /**
     * @param  array<string, mixed>  $parts
     */
    abstract protected function withParts(array $parts): static;

    public function overlay(?self $base): static
    {
        if (is_null($base) || $base::class !== static::class) {
            return $this;
        }

        $parts = $this->parts();

        foreach ($base->parts() as $key => $value) {
            $parts[$key] ??= $value;
        }

        return $this->withParts($parts);
    }

    public function isEmpty(): bool
    {
        foreach ($this->parts() as $value) {
            if (! is_null($value)) {
                return false;
            }
        }

        return true;
    }

    protected static function string(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }

    protected static function bool(mixed $value): ?bool
    {
        return is_bool($value) ? $value : null;
    }
}
