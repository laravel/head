<?php

declare(strict_types=1);

namespace Laravel\Head\Metadata;

use Illuminate\Contracts\Pagination\Paginator;

/**
 * @phpstan-consistent-constructor
 */
class PaginationLinks extends Metadata
{
    /**
     * @param  array<string, string>  $links
     */
    public function __construct(protected array $links = []) {}

    public static function key(): string
    {
        return 'pagination';
    }

    /**
     * @param  Paginator<int, mixed>  $paginator
     */
    public static function fromPaginator(Paginator $paginator): self
    {
        $links = new self;

        if ($previous = $paginator->previousPageUrl()) {
            $links->link('prev', $previous);
        }

        if ($next = $paginator->nextPageUrl()) {
            $links->link('next', $next);
        }

        return $links;
    }

    public function link(string $rel, string $href): static
    {
        $this->links[$rel] = $href;

        return $this;
    }

    public function overlay(?Metadata $base): static
    {
        if (! $base instanceof self) {
            return $this;
        }

        return new static(array_replace($base->links, $this->links));
    }

    /**
     * @return array<string, string>
     */
    public function payload(): array
    {
        return $this->links;
    }

    /**
     * @return array<string, string>
     */
    public function toPayload(ResolvedHead $head): array
    {
        return $this->payload();
    }

    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return array_map(
            fn (string $rel, string $href): string => $tags->link($rel, $href),
            array_keys($this->links),
            $this->links,
        );
    }

    public function isEmpty(): bool
    {
        return $this->links === [];
    }

    /**
     * @return array{links: array<string, string>}
     */
    protected function parts(): array
    {
        return ['links' => $this->links];
    }

    /**
     * @param  array<string, mixed>  $parts
     */
    protected function withParts(array $parts): static
    {
        return new static(self::stringArray($parts['links'] ?? null));
    }

    /**
     * @return array<string, string>
     */
    protected static function stringArray(mixed $value): array
    {
        return is_array($value) ? array_filter($value, fn (mixed $item): bool => is_string($item)) : [];
    }
}
