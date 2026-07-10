<?php

declare(strict_types=1);

namespace Laravel\Head;

/**
 * The request-scoped head data and response status.
 */
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

    public function status(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function flush(): void
    {
        $this->data = new HeadData;
        $this->status = null;
    }
}
