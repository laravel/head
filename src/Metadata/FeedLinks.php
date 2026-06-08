<?php

declare(strict_types=1);

namespace Laravel\Head\Metadata;

use Laravel\Head\Rendering\ResolvedHead;
use Laravel\Head\Rendering\TagRenderer;

/**
 * @phpstan-consistent-constructor
 *
 * @phpstan-type FeedAttributes array{href: string, title: string, type: string}
 */
class FeedLinks extends Metadata
{
    /**
     * @param  array<string, FeedAttributes>  $feeds
     */
    public function __construct(protected array $feeds = []) {}

    public static function key(): string
    {
        return 'feeds';
    }

    public static function payloadKey(): string
    {
        return 'links.feeds';
    }

    public static function attributeKeys(): array
    {
        return ['feed'];
    }

    public static function fromAttributeValue(string $key, mixed $value): ?self
    {
        return $key === 'feed' && is_array($value) ? self::fromAttributes($value) : null;
    }

    /**
     * @param  array<mixed, mixed>  $feeds
     */
    public static function fromAttributes(array $feeds): self
    {
        $links = new self;

        foreach ($feeds as $href => $feed) {
            if (is_string($href) && is_string($feed)) {
                $links->feed($href, $feed);
            }

            if (is_array($feed) && is_string($feed['title'] ?? null)) {
                $links->feed(
                    self::string($feed['href'] ?? null) ?? self::string($href) ?? '',
                    $feed['title'],
                    self::string($feed['type'] ?? null) ?? 'rss',
                );
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

    public function overlay(?Metadata $base): static
    {
        if (! $base instanceof self) {
            return $this;
        }

        return new static(array_replace($base->feeds, $this->feeds));
    }

    /**
     * @return array<int, FeedAttributes>
     */
    public function payload(): array
    {
        return array_values($this->feeds);
    }

    /**
     * @return array<int, FeedAttributes>
     */
    public function toPayload(ResolvedHead $head): array
    {
        return $this->payload();
    }

    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return array_map(function (array $feed): string {
            $type = $feed['type'] === 'atom' ? 'application/atom+xml' : 'application/rss+xml';

            return '<link rel="alternate" type="'.e($type).'" title="'.e($feed['title']).'" href="'.e($feed['href']).'">';
        }, $this->payload());
    }

    public function isEmpty(): bool
    {
        return $this->feeds === [];
    }

    /**
     * @return array{feeds: array<string, FeedAttributes>}
     */
    protected function parts(): array
    {
        return ['feeds' => $this->feeds];
    }

    /**
     * @param  array<string, mixed>  $parts
     */
    protected function withParts(array $parts): static
    {
        return new static(self::feedsArray($parts['feeds'] ?? null));
    }

    /**
     * @return array<string, FeedAttributes>
     */
    protected static function feedsArray(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $feeds = [];

        foreach ($value as $key => $feed) {
            if (is_string($key) && is_array($feed) && is_string($feed['href'] ?? null) && is_string($feed['title'] ?? null) && is_string($feed['type'] ?? null)) {
                $feeds[$key] = [
                    'href' => $feed['href'],
                    'title' => $feed['title'],
                    'type' => $feed['type'],
                ];
            }
        }

        return $feeds;
    }
}
