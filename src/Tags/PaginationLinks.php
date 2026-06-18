<?php

declare(strict_types=1);

namespace Laravel\Head\Tags;

use Illuminate\Contracts\Pagination\Paginator;
use Laravel\Head\Rendering\ResolvedHead;
use Laravel\Head\Rendering\TagRenderer;

/**
 * @phpstan-consistent-constructor
 */
class PaginationLinks extends GroupedTagBuilder
{
    /**
     * @param  array<string, string>  $links
     */
    public function __construct(protected array $links = []) {}

    public static function key(): string
    {
        return 'pagination';
    }

    public static function headArrayKey(): string
    {
        return 'links.pagination';
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

    public function overlayOn(?TagBuilder $base): static
    {
        if (! $base instanceof self) {
            return $this;
        }

        return new static(array_replace($base->links, $this->links));
    }

    public function isEmpty(): bool
    {
        return $this->links === [];
    }

    /**
     * @return array<string, string>
     */
    public function toHeadArray(ResolvedHead $head): array
    {
        return $this->headArray();
    }

    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return array_map(
            fn (string $rel, string $href): string => $tags->link($rel, $href, 'pagination:'.$rel),
            array_keys($this->links),
            $this->links,
        );
    }

    /**
     * @return array<string, string>
     */
    protected function headArray(): array
    {
        return $this->links;
    }
}
