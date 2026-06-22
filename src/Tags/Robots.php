<?php

declare(strict_types=1);

namespace Laravel\Head\Tags;

use Laravel\Head\Enums\RobotsRule;
use Laravel\Head\Rendering\ResolvedHead;
use Laravel\Head\Rendering\TagRenderer;

class Robots extends StringTagBuilder
{
    public static function key(): string
    {
        return 'robots';
    }

    /**
     * @param  string|RobotsRule|array<mixed, mixed>  $directives
     */
    public static function make(string|RobotsRule|array $directives): static
    {
        return new static(self::renderDirectives($directives));
    }

    public static function fromRouteAttribute(string $key, mixed $value): ?self
    {
        if ($key !== static::key()) {
            return null;
        }

        return is_string($value) || $value instanceof RobotsRule || is_array($value)
            ? static::make($value)
            : null;
    }

    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return ($robots = $this->render()) ? [$tags->meta('name', 'robots', $robots, 'robots')] : [];
    }

    /**
     * @param  string|RobotsRule|array<mixed, mixed>  $directives
     */
    protected static function renderDirectives(string|RobotsRule|array $directives): string
    {
        if (is_string($directives)) {
            return $directives;
        }

        if ($directives instanceof RobotsRule) {
            return $directives->value;
        }

        return implode(', ', array_values(array_filter(array_map(
            fn (mixed $directive): ?string => match (true) {
                $directive instanceof RobotsRule => $directive->value,
                is_string($directive) => $directive,
                default => null,
            },
            $directives,
        ))));
    }
}
