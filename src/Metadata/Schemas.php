<?php

declare(strict_types=1);

namespace Laravel\Head\Metadata;

use Laravel\Head\Schema\SchemaObject;

/**
 * @phpstan-consistent-constructor
 *
 * @phpstan-type SchemaPayload array<string, mixed>
 */
class Schemas extends Metadata
{
    /**
     * @param  array<string, SchemaObject|SchemaPayload>  $schemas
     */
    public function __construct(protected array $schemas = []) {}

    public static function key(): string
    {
        return 'schemas';
    }

    public static function attributeKeys(): array
    {
        return ['schema'];
    }

    public static function fromAttributeValue(string $key, mixed $value): ?self
    {
        return $key === 'schema' && ($value instanceof SchemaObject || is_array($value))
            ? self::fromAttributes($value)
            : null;
    }

    /**
     * @param  SchemaObject|SchemaPayload|array<int, SchemaObject|SchemaPayload>  $schemas
     */
    public static function fromAttributes(SchemaObject|array $schemas): self
    {
        $metadata = new self;

        if ($schemas instanceof SchemaObject || ! array_is_list($schemas)) {
            return $metadata->schema($schemas instanceof SchemaObject ? $schemas : self::named($schemas));
        }

        foreach ($schemas as $schema) {
            if ($schema instanceof SchemaObject || is_array($schema)) {
                $metadata->schema($schema instanceof SchemaObject ? $schema : self::named($schema));
            }
        }

        return $metadata;
    }

    /**
     * @param  SchemaObject|SchemaPayload  $schema
     */
    public function schema(SchemaObject|array $schema): static
    {
        $this->schemas[$schema instanceof SchemaObject ? spl_object_hash($schema) : md5(json_encode($schema, JSON_THROW_ON_ERROR))] = $schema;

        return $this;
    }

    public function overlay(?Metadata $base): static
    {
        if (! $base instanceof self) {
            return $this;
        }

        return new static(array_replace($base->schemas, $this->schemas));
    }

    /**
     * @return array<int, SchemaObject|SchemaPayload>
     */
    public function payload(): array
    {
        return array_values($this->schemas);
    }

    /**
     * @return array<int, SchemaPayload>
     */
    public function toPayload(ResolvedHead $head): array
    {
        return array_map(
            fn (SchemaObject|array $schema): array => $schema instanceof SchemaObject ? $schema->toJsonLd() : $schema,
            $this->payload(),
        );
    }

    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return array_map(
            fn (array $schema): string => $tags->jsonLd($schema),
            $this->toPayload($head),
        );
    }

    public function isEmpty(): bool
    {
        return $this->schemas === [];
    }

    /**
     * @return array{schemas: array<string, SchemaObject|SchemaPayload>}
     */
    protected function parts(): array
    {
        return ['schemas' => $this->schemas];
    }

    /**
     * @param  array<string, mixed>  $parts
     */
    protected function withParts(array $parts): static
    {
        return new static(self::schemasArray($parts['schemas'] ?? null));
    }

    /**
     * @param  array<mixed, mixed>  $values
     * @return array<string, mixed>
     */
    protected static function named(array $values): array
    {
        $named = [];

        foreach ($values as $key => $value) {
            if (is_string($key)) {
                $named[$key] = $value;
            }
        }

        return $named;
    }

    /**
     * @return array<string, SchemaObject|SchemaPayload>
     */
    protected static function schemasArray(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $schemas = [];

        foreach ($value as $key => $schema) {
            if (is_string($key) && ($schema instanceof SchemaObject || is_array($schema))) {
                $schemas[$key] = $schema instanceof SchemaObject ? $schema : self::named($schema);
            }
        }

        return $schemas;
    }
}
