<?php

declare(strict_types=1);

namespace Laravel\Head\Metadata;

/**
 * @phpstan-consistent-constructor
 */
class Robots extends Metadata
{
    public function __construct(protected ?string $directives = null) {}

    public static function key(): string
    {
        return 'robots';
    }

    public static function fromAttributeValue(string $key, mixed $value): ?self
    {
        return $key === 'robots' ? self::fromAttributes($value) : null;
    }

    public static function make(string $directives): self
    {
        return new self($directives);
    }

    public static function fromAttributes(mixed $robots): ?self
    {
        return is_string($robots) ? self::make($robots) : null;
    }

    public function render(): ?string
    {
        return $this->directives;
    }

    public function toPayload(ResolvedHead $head): ?string
    {
        return $this->render();
    }

    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return ($robots = $this->render()) ? [$tags->meta('name', 'robots', $robots)] : [];
    }

    /**
     * @return array{directives: string|null}
     */
    protected function parts(): array
    {
        return [
            'directives' => $this->directives,
        ];
    }

    /**
     * @param  array<string, mixed>  $parts
     */
    protected function withParts(array $parts): static
    {
        return new static(self::string($parts['directives'] ?? null));
    }
}
