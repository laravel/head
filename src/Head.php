<?php

declare(strict_types=1);

namespace Laravel\Head;

class Head
{
    /**
     * Register global head defaults.
     */
    public function defaults(callable $callback): static
    {
        $callback($this);

        return $this;
    }

    /**
     * Set the page title.
     */
    public function title(?string $title = null): static
    {
        return $this;
    }

    /**
     * Set the page description.
     */
    public function description(?string $description = null): static
    {
        return $this;
    }

    /**
     * Render the current head tags.
     */
    public function render(): string
    {
        return '';
    }
}
