<?php

declare(strict_types=1);

namespace Laravel\Head\Tags;

use Laravel\Head\Rendering\ResolvedHead;
use Laravel\Head\Rendering\TagRenderer;

/**
 * @phpstan-consistent-constructor
 */
class Title extends TagBuilder
{
    public function __construct(
        protected ?string $value = null,
        protected ?bool $bare = null,
        protected ?string $prefix = null,
        protected ?string $suffix = null,
    ) {}

    public static function key(): string
    {
        return 'title';
    }

    public static function make(string $value, ?string $prefix = null, ?string $suffix = null, ?bool $bare = null): self
    {
        return new self($value, $bare ?? false, $prefix, $suffix);
    }

    public static function fromRouteAttribute(string $key, mixed $value): ?self
    {
        if ($key !== 'title') {
            return null;
        }

        $title = $value;

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

    public function overlayOn(?TagBuilder $base): static
    {
        if (! $base instanceof static) {
            return $this;
        }

        return new static(
            $this->value ?? $base->value,
            $this->bare ?? $base->bare,
            $this->prefix ?? $base->prefix,
            $this->suffix ?? $base->suffix,
        );
    }

    public function asDefaults(): static
    {
        return is_null($this->value)
            ? $this
            : new static($this->value, true, $this->prefix, $this->suffix);
    }

    public function isEmpty(): bool
    {
        return is_null($this->value)
            && is_null($this->bare)
            && is_null($this->prefix)
            && is_null($this->suffix);
    }

    public function render(): ?string
    {
        if (is_null($this->value)) {
            return null;
        }

        if ($this->bare === true) {
            return $this->value;
        }

        return ($this->prefix ?? '').$this->value.($this->suffix ?? '');
    }

    public function toHeadArray(ResolvedHead $head): ?string
    {
        return $this->render();
    }

    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return ($title = $this->render()) ? [$tags->title($title, 'title')] : [];
    }
}
