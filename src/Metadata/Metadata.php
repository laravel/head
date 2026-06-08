<?php

declare(strict_types=1);

namespace Laravel\Head\Metadata;

abstract class Metadata
{
    abstract public static function key(): string;

    /**
     * @return array<string, mixed>
     */
    abstract protected function parts(): array;

    /**
     * @param  array<string, mixed>  $parts
     */
    abstract protected function withParts(array $parts): static;

    /**
     * @return array<int, string>
     */
    public static function attributeKeys(): array
    {
        return [static::key()];
    }

    public static function fromAttributeValue(string $key, mixed $value): ?self
    {
        return null;
    }

    public function overlay(?self $base): static
    {
        if (! $base instanceof static) {
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

    public function asDefaults(): static
    {
        return $this;
    }

    public function toPayload(ResolvedHead $head): mixed
    {
        return null;
    }

    /**
     * @return array<int, string>
     */
    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return [];
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
