<?php

declare(strict_types=1);

namespace Laravel\Head\Metadata;

/**
 * @phpstan-consistent-constructor
 */
class Description extends Metadata
{
    public function __construct(protected ?string $description = null) {}

    public static function key(): string
    {
        return 'description';
    }

    public static function fromAttributeValue(string $key, mixed $value): ?self
    {
        return $key === 'description' ? self::fromAttributes($value) : null;
    }

    public static function make(string $description): self
    {
        return new self($description);
    }

    public static function fromAttributes(mixed $value): ?self
    {
        return is_string($value) ? self::make($value) : null;
    }

    public function render(): ?string
    {
        return $this->description;
    }

    public function toPayload(ResolvedHead $head): ?string
    {
        return $this->render();
    }

    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return ($description = $this->render()) ? [$tags->meta('name', 'description', $description)] : [];
    }

    /**
     * @return array{description: string|null}
     */
    protected function parts(): array
    {
        return ['description' => $this->description];
    }

    /**
     * @param  array<string, mixed>  $parts
     */
    protected function withParts(array $parts): static
    {
        return new static(self::string($parts['description'] ?? null));
    }
}
