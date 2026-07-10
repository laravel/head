<?php

declare(strict_types=1);

namespace Laravel\Head\Facades;

use Illuminate\Support\Facades\Facade;
use Laravel\Head\Enums\RobotsRule;
use Laravel\Head\HeadManager;
use Laravel\Head\Routing\RouteAttributeParser;

/**
 * @method static \Laravel\Head\HeadManager inertiaGlobals(callable $callback)
 * @method static \Laravel\Head\HeadManager defaults(callable $callback)
 * @method static \Laravel\Head\HeadManager errors(callable $callback)
 * @method static \Laravel\Head\HeadManager inertia(string $prop = 'head')
 * @method static \Laravel\Head\HeadManager extend(class-string<\Laravel\Head\Tags\TagBuilder> $builder)
 * @method static \Laravel\Head\HeadManager status(int $status)
 * @method static \Laravel\Head\HeadManager title(string $title, ?string $prefix = null, ?string $suffix = null, ?bool $exact = null)
 * @method static \Laravel\Head\HeadManager description(string $description)
 * @method static \Laravel\Head\HeadManager themeColor(string $color, \Laravel\Head\Enums\Media|string|null $media = null)
 * @method static \Laravel\Head\HeadManager applicationName(string $name)
 * @method static \Laravel\Head\HeadManager colorScheme(string $scheme)
 * @method static \Laravel\Head\HeadManager referrer(string $policy)
 * @method static \Laravel\Head\HeadManager viewport(string $content)
 * @method static \Laravel\Head\HeadManager appleWebAppTitle(string $title)
 * @method static \Laravel\Head\HeadManager webAppCapable(bool $capable = true)
 * @method static \Laravel\Head\HeadManager appleWebAppStatusBarStyle(string $style)
 * @method static \Laravel\Head\HeadManager canonical(?string $url = null, ?bool $forceHttps = null, ?bool $trailingSlash = null)
 * @method static \Laravel\Head\HeadManager robots(string|\Laravel\Head\Enums\RobotsRule|array<int, string|\Laravel\Head\Enums\RobotsRule> $directives)
 * @method static \Laravel\Head\HeadManager robotsSearchable()
 * @method static \Laravel\Head\HeadManager robotsHidden()
 * @method static \Laravel\Head\HeadManager og(\Laravel\Head\Enums\OgType|string|null $type = null, ?string $title = null, ?string $description = null, ?string $url = null, ?string $image = null, ?string $video = null, ?string $audio = null, ?string $siteName = null, ?string $locale = null, ?string $determiner = null)
 * @method static \Laravel\Head\HeadManager ogImage(string $url, ?string $alt = null, ?int $width = null, ?int $height = null, \Laravel\Head\Enums\ImageType|string|null $type = null, ?string $secureUrl = null)
 * @method static \Laravel\Head\HeadManager ogVideo(string $url, ?string $alt = null, ?int $width = null, ?int $height = null, ?string $type = null, ?string $secureUrl = null)
 * @method static \Laravel\Head\HeadManager ogAudio(string $url, ?string $type = null, ?string $secureUrl = null)
 * @method static \Laravel\Head\HeadManager twitter(\Laravel\Head\Enums\TwitterCard|string|null $card = null, ?string $site = null, ?string $creator = null, ?string $title = null, ?string $description = null, ?string $image = null)
 * @method static \Laravel\Head\HeadManager twitterImage(string $url, ?string $alt = null)
 * @method static \Laravel\Head\HeadManager preload(string $href, ?string $as = null, bool|string|null $crossorigin = null, \Laravel\Head\Enums\ImageType|string|null $type = null, \Laravel\Head\Enums\Media|string|null $media = null)
 * @method static \Laravel\Head\HeadManager prefetch(string $href, ?string $as = null)
 * @method static \Laravel\Head\HeadManager preconnect(string $href, bool|string|null $crossorigin = null)
 * @method static \Laravel\Head\HeadManager dnsPrefetch(string $href)
 * @method static \Laravel\Head\HeadManager paginate(\Illuminate\Contracts\Pagination\Paginator<int, mixed> $paginator)
 * @method static \Laravel\Head\HeadManager alternates(array<string, string> $alternates)
 * @method static \Laravel\Head\HeadManager feed(string $href, string $title, string $type = 'rss')
 * @method static \Laravel\Head\HeadManager meta(string $key, string $content, ?bool $property = null, \Laravel\Head\Enums\Media|string|null $media = null)
 * @method static \Laravel\Head\HeadManager link(string $rel, string $href, array<string, \BackedEnum|bool|float|int|string|null> $attributes = [])
 * @method static \Laravel\Head\HeadManager icon(string $href, \Laravel\Head\Enums\ImageType|string|null $type = null, ?string $sizes = null, \Laravel\Head\Enums\Media|string|null $media = null)
 * @method static \Laravel\Head\HeadManager appleTouchIcon(string $href, ?string $sizes = null)
 * @method static \Laravel\Head\HeadManager maskIcon(string $href, ?string $color = null)
 * @method static \Laravel\Head\HeadManager manifest(string $href = '/site.webmanifest', bool|string|null $crossorigin = null)
 * @method static \Laravel\Head\HeadManager appleTouchStartupImage(string $href, \Laravel\Head\Enums\Media|string|null $media = null)
 * @method static \Laravel\Head\HeadManager pwa(string $name, string $manifest = '/site.webmanifest', ?string $themeColor = null, ?string $appleTouchIcon = null, ?string $appleTouchIconSizes = '180x180', ?string $appleWebAppStatusBarStyle = null)
 * @method static \Laravel\Head\HeadManager schema(\Laravel\Head\Schema\SchemaObject|array<string, mixed>|callable $schema)
 * @method static array<string, mixed> toArray(?int $status = null)
 * @method static array<int, string> toElements(?int $status = null)
 * @method static array<int, string> toInertiaElements(?int $status = null)
 * @method static \Illuminate\Support\HtmlString render(?int $status = null)
 * @method static \Illuminate\Support\HtmlString renderForView(array<string, mixed> $variables = [], ?int $status = null)
 * @method static string toHtml(?int $status = null)
 * @method static \Laravel\Head\HeadManager flush()
 * @method static \Laravel\Head\HeadManager|mixed when(mixed $value = null, callable $callback = null, callable $default = null)
 * @method static \Laravel\Head\HeadManager|mixed unless(mixed $value = null, callable $callback = null, callable $default = null)
 * @method static void macro(string $name, object|callable $macro)
 * @method static void mixin(object $mixin, bool $replace = true)
 *
 * @see HeadManager
 *
 * @mixin HeadManager
 */
class Head extends Facade
{
    /**
     * Create cache-friendly Laravel route metadata for document head data.
     *
     * @param  array<mixed, mixed>|string|null  $title
     * @param  array<mixed, mixed>|string|true|null  $canonical
     * @param  array<int, string|RobotsRule>|string|RobotsRule|null  $robots
     * @param  array<string, mixed>|null  $og
     * @param  array<mixed, mixed>|string|null  $ogImage
     * @param  array<mixed, mixed>|string|null  $ogVideo
     * @param  array<mixed, mixed>|string|null  $ogAudio
     * @param  array<string, mixed>|null  $twitter
     * @param  array<mixed, mixed>|string|null  $twitterImage
     * @param  array<mixed, mixed>|string|null  $preload
     * @param  array<mixed, mixed>|string|null  $prefetch
     * @param  array<mixed, mixed>|string|null  $preconnect
     * @param  array<mixed, mixed>|string|null  $dnsPrefetch
     * @param  array<string, string>|null  $alternates
     * @param  array<mixed, mixed>|string|null  $feed
     * @param  array<mixed, mixed>|string|null  $icon
     * @param  array<mixed, mixed>|string|null  $appleTouchIcon
     * @param  array<mixed, mixed>|string|null  $appleTouchStartupImage
     * @param  array<mixed, mixed>|string|null  $maskIcon
     * @param  array<mixed, mixed>|string|null  $manifest
     * @param  array<mixed, mixed>|null  $schema
     * @param  array<mixed, mixed>|null  $meta
     * @param  array<mixed, mixed>|null  $link
     * @param  array<string, mixed>  $extensions
     * @return array<string, array<string, array<string, mixed>>>
     */
    public static function forMetadata(
        string|array|null $title = null,
        ?string $description = null,
        string|array|true|null $canonical = null,
        string|RobotsRule|array|null $robots = null,
        ?string $themeColor = null,
        ?string $applicationName = null,
        ?string $colorScheme = null,
        ?string $referrer = null,
        ?string $viewport = null,
        ?string $appleWebAppTitle = null,
        ?bool $webAppCapable = null,
        ?string $appleWebAppStatusBarStyle = null,
        ?array $og = null,
        string|array|null $ogImage = null,
        string|array|null $ogVideo = null,
        string|array|null $ogAudio = null,
        ?array $twitter = null,
        string|array|null $twitterImage = null,
        string|array|null $preload = null,
        string|array|null $prefetch = null,
        string|array|null $preconnect = null,
        string|array|null $dnsPrefetch = null,
        ?array $alternates = null,
        string|array|null $feed = null,
        string|array|null $icon = null,
        string|array|null $appleTouchIcon = null,
        string|array|null $appleTouchStartupImage = null,
        string|array|null $maskIcon = null,
        string|array|null $manifest = null,
        ?array $schema = null,
        ?array $meta = null,
        ?array $link = null,
        array $extensions = [],
    ): array {
        $values = [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => $robots,
            'themeColor' => $themeColor,
            'applicationName' => $applicationName,
            'colorScheme' => $colorScheme,
            'referrer' => $referrer,
            'viewport' => $viewport,
            'appleWebAppTitle' => $appleWebAppTitle,
            'webAppCapable' => $webAppCapable,
            'appleWebAppStatusBarStyle' => $appleWebAppStatusBarStyle,
            'og' => $og,
            'ogImage' => $ogImage,
            'ogVideo' => $ogVideo,
            'ogAudio' => $ogAudio,
            'twitter' => $twitter,
            'twitterImage' => $twitterImage,
            'preload' => $preload,
            'prefetch' => $prefetch,
            'preconnect' => $preconnect,
            'dnsPrefetch' => $dnsPrefetch,
            'alternates' => $alternates,
            'feed' => $feed,
            'icon' => $icon,
            'appleTouchIcon' => $appleTouchIcon,
            'appleTouchStartupImage' => $appleTouchStartupImage,
            'maskIcon' => $maskIcon,
            'manifest' => $manifest,
            'schema' => $schema,
            'meta' => $meta,
            'link' => $link,
        ] + $extensions;

        $attributes = [];

        foreach ($values as $key => $value) {
            if (! is_null($value)) {
                $attributes[$key] = $value;
            }
        }

        return RouteAttributeParser::metadata($attributes);
    }

    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'head';
    }
}
