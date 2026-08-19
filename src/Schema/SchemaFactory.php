<?php

declare(strict_types=1);

namespace Laravel\Head\Schema;

use Illuminate\Support\Str;
use ReflectionClass;

class SchemaFactory
{
    /** @var array<string, class-string<SchemaObject>> */
    protected array $types = [];

    public function __construct()
    {
        $this->register(Article::class);
        $this->register(BlogPosting::class);
        $this->register(Product::class);
        $this->register(Offer::class);
        $this->register(Brand::class);
        $this->register(Breadcrumbs::class);
        $this->register(Faq::class);
        $this->register(Organization::class);
        $this->register(Person::class);
        $this->register(WebPage::class);
        $this->register(WebSite::class);
        $this->register(Event::class);
    }

    /**
     * Register a first-class schema object type.
     *
     * @param  class-string<SchemaObject>  $class
     */
    public function register(string $class): static
    {
        $this->types[Str::camel((new ReflectionClass($class))->getShortName())] = $class;

        return $this;
    }

    /**
     * Make a schema object from a schema object class or registered type name.
     *
     * @template TSchema of SchemaObject
     *
     * @param  class-string<TSchema>|string  $type
     * @return ($type is class-string<TSchema> ? TSchema : SchemaObject)
     */
    public function make(string $type): SchemaObject
    {
        if (is_subclass_of($type, SchemaObject::class)) {
            return new $type;
        }

        $class = $this->types[Str::camel($type)] ?? null;

        return $class ? new $class : new GenericSchemaObject(Str::studly($type));
    }

    /**
     * Dynamically make a schema object for the called type name.
     *
     * @param  array<int, mixed>  $parameters
     */
    public function __call(string $method, array $parameters): SchemaObject
    {
        return $this->make($method);
    }
}
