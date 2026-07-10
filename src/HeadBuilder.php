<?php

declare(strict_types=1);

namespace Laravel\Head;

use Illuminate\Support\Traits\Conditionable;
use Laravel\Head\Concerns\BuildsHead;
use Laravel\Head\Schema\SchemaFactory;

class HeadBuilder
{
    use BuildsHead;
    use Conditionable;

    public function __construct(
        protected HeadData $data,
        protected ?SchemaFactory $schemaFactory = null,
    ) {}

    protected function headData(): HeadData
    {
        return $this->data;
    }

    protected function schemaFactory(): SchemaFactory
    {
        return $this->schemaFactory ??= new SchemaFactory;
    }
}
