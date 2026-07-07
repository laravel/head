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
        $this->register(Breadcrumbs::class);
        $this->register(Faq::class);
        $this->register(Organization::class);
        $this->register(Person::class);
        $this->register(WebPage::class);
        $this->register(WebSite::class);
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

    public function make(string $type): SchemaObject
    {
        $class = $this->types[Str::camel($type)] ?? null;

        return $class ? new $class : new GenericSchemaObject(Str::studly($type));
    }

    /**
     * @param  array<int, mixed>  $parameters
     */
    public function __call(string $method, array $parameters): SchemaObject
    {
        return $this->make($method);
    }
}
