<?php

declare(strict_types=1);

namespace Laravel\Head\Schema;

use BadMethodCallException;
use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use JsonSerializable;
use Laravel\Head\SchemaType;
use ReflectionClass;

/**
 * @implements Arrayable<string, mixed>
 */
abstract class SchemaObject implements Arrayable, JsonSerializable
{
    protected string $type;

    /** @var array<string, mixed> */
    protected array $properties = [];

    public function __construct(?string $type = null)
    {
        $this->type = $type ?? $this->typeFromAttribute();
    }

    public function set(string $property, mixed $value): static
    {
        $this->properties[$property] = $this->coerce($value);

        return $this;
    }

    public function date(string $property, DateTimeInterface|string $date): static
    {
        return $this->set($property, $date instanceof DateTimeInterface ? $date->format(DateTimeInterface::ATOM) : $date);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['@type' => $this->type] + $this->properties;
    }

    /**
     * @return array<string, mixed>
     */
    public function toJsonLd(): array
    {
        return ['@context' => 'https://schema.org'] + $this->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toJsonLd();
    }

    /**
     * @param  array<int, mixed>  $parameters
     */
    public function __call(string $method, array $parameters): static
    {
        if (count($parameters) > 1) {
            throw new BadMethodCallException(sprintf(
                'Magic schema property setters accept a single value, and [%s] does not define a [%s] method.',
                static::class,
                $method,
            ));
        }

        return $this->set($method, $parameters[0] ?? true);
    }

    protected function coerce(mixed $value): mixed
    {
        if ($value instanceof SchemaObject) {
            return $value->toArray();
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format(DateTimeInterface::ATOM);
        }

        if (is_array($value)) {
            return array_map($this->coerce(...), $value);
        }

        return $value;
    }

    protected function typeFromAttribute(): string
    {
        $reflection = new ReflectionClass($this);
        $attributes = $reflection->getAttributes(SchemaType::class);

        return $attributes === []
            ? Str::headline($reflection->getShortName())
            : $attributes[0]->newInstance()->name;
    }
}
