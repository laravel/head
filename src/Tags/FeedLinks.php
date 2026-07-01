<?php

declare(strict_types=1);

namespace Laravel\Head\Tags;

use Laravel\Head\Rendering\ResolvedHead;
use Laravel\Head\Rendering\TagRenderer;

/**
 * @phpstan-consistent-constructor
 *
 * @phpstan-type FeedAttributes array{href: string, title: string, type: string}
 */
class FeedLinks extends GroupedTagBuilder
{
    /**
     * @param  array<string, FeedAttributes>  $feeds
     */
    public function __construct(protected array $feeds = [])
    {
        //
    }

    public static function key(): string
    {
        return 'feeds';
    }

    public static function headArrayKey(): string
    {
        return 'links.feeds';
    }

    public static function routeAttributeKeys(): array
    {
        return ['feed'];
    }

    public static function fromRouteAttribute(string $key, mixed $value): ?self
    {
        if ($key !== 'feed' || ! is_array($value)) {
            return null;
        }

        $links = new self;

        foreach (self::feedAttributes($value) as $href => $feed) {
            if (! $links->addRouteAttributeFeed($href, $feed)) {
                return null;
            }
        }

        return $links;
    }

    public function feed(string $href, string $title, string $type = 'rss'): static
    {
        $this->feeds[$href] = [
            'href' => $href,
            'title' => $title,
            'type' => $type,
        ];

        return $this;
    }

    public function overlayOn(?TagBuilder $base): static
    {
        if (! $base instanceof self) {
            return $this;
        }

        return new static(array_replace($base->feeds, $this->feeds));
    }

    public function isEmpty(): bool
    {
        return $this->feeds === [];
    }

    /**
     * @return array<int, FeedAttributes>
     */
    public function toHeadArray(ResolvedHead $head): array
    {
        return $this->headArray();
    }

    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return array_map(function (array $feed) use ($tags): string {
            $type = $feed['type'] === 'atom' ? 'application/atom+xml' : 'application/rss+xml';

            return $tags->linkWithAttributes('alternate', [
                'type' => $type,
                'title' => $feed['title'],
                'href' => $feed['href'],
            ], $tags->stableKey('feed', $feed['href']));
        }, $this->headArray());
    }

    /**
     * @return array<int, FeedAttributes>
     */
    protected function headArray(): array
    {
        return array_values($this->feeds);
    }

    private function addRouteAttributeFeed(mixed $href, mixed $feed): bool
    {
        if (is_string($href) && is_string($feed)) {
            $this->feed($href, $feed);

            return true;
        }

        if (! is_array($feed) || ! is_string($feed['title'] ?? null) || is_null($url = self::routeAttributeHref($href, $feed))) {
            return false;
        }

        $this->feed($url, $feed['title'], self::string($feed['type'] ?? null) ?? 'rss');

        return true;
    }

    /**
     * @param  array<mixed, mixed>  $value
     * @return array<mixed, mixed>
     */
    private static function feedAttributes(array $value): array
    {
        if (array_is_list($value)) {
            return $value;
        }

        if (array_key_exists('href', $value) || array_key_exists('title', $value) || array_key_exists('type', $value)) {
            return [$value];
        }

        return $value;
    }

    /**
     * @param  array<mixed, mixed>  $feed
     */
    private static function routeAttributeHref(mixed $href, array $feed): ?string
    {
        return self::string($feed['href'] ?? null) ?? self::string($href);
    }
}
