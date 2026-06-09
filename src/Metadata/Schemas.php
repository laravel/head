<?php

declare(strict_types=1);

namespace Laravel\Head\Metadata;

use Laravel\Head\Rendering\ResolvedHead;
use Laravel\Head\Rendering\TagRenderer;
use Laravel\Head\Schema\SchemaObject;

/**
 * @phpstan-consistent-constructor
 *
 * @phpstan-type SchemaData array<string, mixed>
 */
class Schemas extends GroupedSection
{
    /**
     * @param  array<string, SchemaObject|SchemaData>  $schemas
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
     * @param  SchemaObject|SchemaData|array<int, SchemaObject|SchemaData>  $schemas
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
     * @param  SchemaObject|SchemaData  $schema
     */
    public function schema(SchemaObject|array $schema): static
    {
        $this->schemas[$schema instanceof SchemaObject ? spl_object_hash($schema) : md5(json_encode($schema, JSON_THROW_ON_ERROR))] = $schema;

        return $this;
    }

    public function overlayOn(?Section $base): static
    {
        if (! $base instanceof self) {
            return $this;
        }

        return new static(array_replace($base->schemas, $this->schemas));
    }

    /**
     * @return array<int, SchemaObject|SchemaData>
     */
    protected function headArray(): array
    {
        return array_values($this->schemas);
    }

    /**
     * @return array<int, SchemaData>
     */
    public function toHeadArray(ResolvedHead $head): array
    {
        return array_map(function (SchemaObject|array $schema) use ($head): array {
            $schemaData = $schema instanceof SchemaObject ? $schema->toJsonLd() : $schema;

            $head->validateSchema($schemaData);

            return $schemaData;
        }, $this->headArray());
    }

    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return array_map(
            fn (array $schema): string => $tags->jsonLd($schema),
            $this->toHeadArray($head),
        );
    }

    public function isEmpty(): bool
    {
        return $this->schemas === [];
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
}
