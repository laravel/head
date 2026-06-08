<?php

declare(strict_types=1);

namespace Laravel\Head\Metadata;

use Laravel\Head\Rendering\ResolvedHead;

/**
 * @phpstan-consistent-constructor
 */
abstract class StringMetadata extends Metadata
{
    public function __construct(protected ?string $value = null) {}

    public static function make(string $value): static
    {
        return new static($value);
    }

    public static function fromAttributeValue(string $key, mixed $value): ?self
    {
        return $key === static::key() && is_string($value)
            ? static::make($value)
            : null;
    }

    public function render(): ?string
    {
        return $this->value;
    }

    public function toHeadArray(ResolvedHead $head): ?string
    {
        return $this->render();
    }

    /**
     * @return array{value: string|null}
     */
    protected function parts(): array
    {
        return ['value' => $this->value];
    }

    /**
     * @param  array<string, mixed>  $parts
     */
    protected function withParts(array $parts): static
    {
        return new static(self::string($parts['value'] ?? null));
    }
}
