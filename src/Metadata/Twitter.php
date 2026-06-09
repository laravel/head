<?php

declare(strict_types=1);

namespace Laravel\Head\Metadata;

use Illuminate\Support\Arr;
use Laravel\Head\Rendering\ResolvedHead;
use Laravel\Head\Rendering\TagRenderer;
use Laravel\Head\TwitterCard;

/**
 * @phpstan-consistent-constructor
 *
 * @phpstan-type ImageAttributes array<string, string>
 */
class Twitter extends GroupedSection
{
    /**
     * @param  array<string, string>  $properties
     * @param  ImageAttributes|null  $image
     */
    public function __construct(
        protected array $properties = [],
        protected ?array $image = null,
    ) {}

    public static function key(): string
    {
        return 'twitter';
    }

    public static function attributeKeys(): array
    {
        return ['twitter', 'twitterImage'];
    }

    public static function rendersWhenEmpty(ResolvedHead $head): bool
    {
        return ! is_null($head->title())
            || ! is_null($head->description())
            || ! is_null($head->openGraphImage());
    }

    public static function fromAttributeValue(string $key, mixed $value): ?self
    {
        return match ($key) {
            'twitter' => is_array($value) ? self::fromAttributes($value) : null,
            'twitterImage' => self::fromImageAttributes($value),
            default => null,
        };
    }

    public static function make(
        TwitterCard|string|null $card = null,
        ?string $site = null,
        ?string $creator = null,
        ?string $title = null,
        ?string $description = null,
        ?string $image = null,
    ): self {
        $twitter = new self;

        $twitter->set([
            'card' => $card instanceof TwitterCard ? $card->value : $card,
            'site' => $site,
            'creator' => $creator,
            'title' => $title,
            'description' => $description,
        ]);

        if (! is_null($image)) {
            $twitter->image($image);
        }

        return $twitter;
    }

    /**
     * @param  array<mixed, mixed>  $values
     */
    public static function fromAttributes(array $values): self
    {
        return self::make(
            card: self::twitterCard($values['card'] ?? null),
            site: self::string($values['site'] ?? null),
            creator: self::string($values['creator'] ?? null),
            title: self::string($values['title'] ?? null),
            description: self::string($values['description'] ?? null),
            image: self::string($values['image'] ?? null),
        );
    }

    public static function fromImageAttributes(mixed $image): self
    {
        $twitter = new self;

        if (is_string($image)) {
            return $twitter->image($image);
        }

        if (is_array($image) && is_string($image['url'] ?? null)) {
            return $twitter->image($image['url'], alt: self::string($image['alt'] ?? null));
        }

        return $twitter;
    }

    public function image(string $url, ?string $alt = null): static
    {
        $this->image = Arr::whereNotNull([
            'url' => $url,
            'alt' => $alt,
        ]);

        return $this;
    }

    public function overlayOn(?Section $base): static
    {
        if (! $base instanceof self) {
            return $this;
        }

        return new static(
            array_replace($base->properties, $this->properties),
            $this->image ?? $base->image,
        );
    }

    /**
     * @return array<string, string>
     */
    public function render(?string $title = null, ?string $description = null): array
    {
        $properties = $this->properties;

        if ($title && ! isset($properties['title'])) {
            $properties['title'] = $title;
        }

        if ($description && ! isset($properties['description'])) {
            $properties['description'] = $description;
        }

        return $properties;
    }

    /**
     * @param  ImageAttributes|null  $fallback
     * @return ImageAttributes|null
     */
    protected function headArrayImage(?array $fallback = null): ?array
    {
        return $this->image ?? $fallback;
    }

    /**
     * @param  ImageAttributes|null  $fallbackImage
     * @return array<string, mixed>
     */
    protected function headArray(?string $title = null, ?string $description = null, ?array $fallbackImage = null): array
    {
        return array_filter([
            ...$this->render($title, $description),
            'image' => $this->headArrayImage($fallbackImage),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toHeadArray(ResolvedHead $head): array
    {
        return $this->headArray($head->title(), $head->description(), $head->openGraphImage());
    }

    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        $properties = $this->render($head->title(), $head->description());
        $rendered = array_map(
            fn (string $name, string $content): string => $tags->meta('name', 'twitter:'.$name, $content),
            array_keys($properties),
            $properties,
        );

        if (is_null($image = $this->headArrayImage($head->openGraphImage()))) {
            return $rendered;
        }

        $rendered[] = $tags->meta('name', 'twitter:image', $image['url']);

        if (isset($image['alt'])) {
            $rendered[] = $tags->meta('name', 'twitter:image:alt', $image['alt']);
        }

        return $rendered;
    }

    public function isEmpty(): bool
    {
        return $this->properties === [] && is_null($this->image);
    }

    /**
     * @param  array<string, string|null>  $properties
     */
    protected function set(array $properties): void
    {
        foreach ($properties as $property => $value) {
            if (! is_null($value)) {
                $this->properties[$property] = $value;
            }
        }
    }

    protected static function twitterCard(mixed $value): TwitterCard|string|null
    {
        return $value instanceof TwitterCard || is_string($value) ? $value : null;
    }
}
