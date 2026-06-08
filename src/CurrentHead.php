<?php

declare(strict_types=1);

namespace Laravel\Head;

class CurrentHead
{
    protected HeadData $data;

    protected ?int $status = null;

    public function __construct()
    {
        $this->flush();
    }

    public function data(): HeadData
    {
        return $this->data;
    }

    public function status(?int $status = null): ?int
    {
        if (! is_null($status)) {
            $this->status = $status;
        }

        return $this->status;
    }

    public function flush(): void
    {
        $this->data = new HeadData;
        $this->status = null;
    }
}
