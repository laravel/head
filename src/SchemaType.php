<?php

declare(strict_types=1);

namespace Laravel\Head;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class SchemaType
{
    public function __construct(public string $name) {}
}
