<?php

declare(strict_types=1);

namespace Laravel\Head\Metadata;

/**
 * @phpstan-consistent-constructor
 */
class Title extends Metadata
{
    public function __construct(
        protected ?string $value = null,
        protected ?bool $decorated = null,
        protected ?string $prefix = null,
        protected ?string $suffix = null,
    ) {}

    public static function key(): string
    {
        return 'title';
    }

    public static function fromAttributeValue(string $key, mixed $value): ?self
    {
        return $key === 'title' ? self::fromAttributes($value) : null;
    }

    public static function make(string $value, ?string $prefix = null, ?string $suffix = null, ?bool $bare = null): self
    {
        return new self($value, ! ($bare ?? false), $prefix, $suffix);
    }

    public static function fromAttributes(mixed $title): ?self
    {
        if (is_string($title)) {
            return self::make($title);
        }

        if (! is_array($title) || ! is_string($title['value'] ?? null)) {
            return null;
        }

        return self::make(
            $title['value'],
            prefix: self::string($title['prefix'] ?? null),
            suffix: self::string($title['suffix'] ?? null),
            bare: self::bool($title['bare'] ?? null),
        );
    }

    public function asDefaults(): static
    {
        return is_null($this->value)
            ? $this
            : new static($this->value, false, $this->prefix, $this->suffix);
    }

    public function render(): ?string
    {
        if (is_null($this->value)) {
            return null;
        }

        if ($this->decorated === false) {
            return $this->value;
        }

        return ($this->prefix ?? '').$this->value.($this->suffix ?? '');
    }

    public function toPayload(ResolvedHead $head): ?string
    {
        return $this->render();
    }

    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return ($title = $this->render()) ? [$tags->title($title)] : [];
    }

    /**
     * @return array{value: string|null, decorated: bool|null, prefix: string|null, suffix: string|null}
     */
    protected function parts(): array
    {
        return [
            'value' => $this->value,
            'decorated' => $this->decorated,
            'prefix' => $this->prefix,
            'suffix' => $this->suffix,
        ];
    }

    /**
     * @param  array<string, mixed>  $parts
     */
    protected function withParts(array $parts): static
    {
        return new static(
            self::string($parts['value'] ?? null),
            self::bool($parts['decorated'] ?? null),
            self::string($parts['prefix'] ?? null),
            self::string($parts['suffix'] ?? null),
        );
    }
}
