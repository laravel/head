<?php

declare(strict_types=1);

namespace Laravel\Head\Facades;

use Illuminate\Support\Facades\Facade;
use Laravel\Head\HeadManager;

/**
 * @method static \Laravel\Head\HeadManager globals(callable $callback)
 * @method static \Laravel\Head\HeadManager defaults(callable $callback)
 * @method static \Laravel\Head\HeadManager errors(callable $callback)
 * @method static \Laravel\Head\HeadManager extend(class-string<\Laravel\Head\Tags\TagBuilder> $builder)
 * @method static \Laravel\Head\HeadManager status(int $status)
 * @method static \Laravel\Head\HeadManager title(string $title, ?string $prefix = null, ?string $suffix = null, ?bool $exact = null)
 * @method static \Laravel\Head\HeadManager description(string $description)
 * @method static \Laravel\Head\HeadManager themeColor(string $color)
 * @method static \Laravel\Head\HeadManager applicationName(string $name)
 * @method static \Laravel\Head\HeadManager colorScheme(string $scheme)
 * @method static \Laravel\Head\HeadManager referrer(string $policy)
 * @method static \Laravel\Head\HeadManager viewport(string $content)
 * @method static \Laravel\Head\HeadManager appleWebAppTitle(string $title)
 * @method static \Laravel\Head\HeadManager webAppCapable(bool $capable = true)
 * @method static \Laravel\Head\HeadManager appleWebAppStatusBarStyle(string $style)
 * @method static \Laravel\Head\HeadManager canonical(string|false|null $url = null, ?bool $forceHttps = null, ?bool $trailingSlash = null)
 * @method static \Laravel\Head\HeadManager robots(string|\Laravel\Head\Enums\RobotsRule|array<int, string|\Laravel\Head\Enums\RobotsRule> $directives)
 * @method static \Laravel\Head\HeadManager og(\Laravel\Head\Enums\OgType|string|null $type = null, ?string $title = null, ?string $description = null, ?string $url = null, ?string $image = null, ?string $video = null, ?string $audio = null, ?string $siteName = null, ?string $locale = null, ?string $determiner = null)
 * @method static \Laravel\Head\HeadManager ogImage(string $url, ?string $alt = null, ?int $width = null, ?int $height = null, \Laravel\Head\Enums\ImageType|string|null $type = null, ?string $secureUrl = null)
 * @method static \Laravel\Head\HeadManager ogVideo(string $url, ?string $alt = null, ?int $width = null, ?int $height = null, ?string $type = null, ?string $secureUrl = null)
 * @method static \Laravel\Head\HeadManager ogAudio(string $url, ?string $type = null, ?string $secureUrl = null)
 * @method static \Laravel\Head\HeadManager twitter(\Laravel\Head\Enums\TwitterCard|string|null $card = null, ?string $site = null, ?string $creator = null, ?string $title = null, ?string $description = null, ?string $image = null)
 * @method static \Laravel\Head\HeadManager twitterImage(string $url, ?string $alt = null)
 * @method static \Laravel\Head\HeadManager preload(string $href, ?string $as = null, bool|string|null $crossorigin = null, \Laravel\Head\Enums\ImageType|string|null $type = null, ?string $media = null)
 * @method static \Laravel\Head\HeadManager prefetch(string $href, ?string $as = null)
 * @method static \Laravel\Head\HeadManager preconnect(string $href, bool|string|null $crossorigin = null)
 * @method static \Laravel\Head\HeadManager dnsPrefetch(string $href)
 * @method static \Laravel\Head\HeadManager paginate(\Illuminate\Contracts\Pagination\Paginator<int, mixed> $paginator)
 * @method static \Laravel\Head\HeadManager alternates(array<string, string> $alternates)
 * @method static \Laravel\Head\HeadManager feed(string $href, string $title, string $type = 'rss')
 * @method static \Laravel\Head\HeadManager meta(string $key, string $content, ?bool $property = null, ?string $media = null)
 * @method static \Laravel\Head\HeadManager link(string $rel, string $href, array<string, \BackedEnum|bool|float|int|string|null> $attributes = [])
 * @method static \Laravel\Head\HeadManager icon(string $href, \Laravel\Head\Enums\ImageType|string|null $type = null, ?string $sizes = null, ?string $media = null)
 * @method static \Laravel\Head\HeadManager appleTouchIcon(string $href, ?string $sizes = null)
 * @method static \Laravel\Head\HeadManager maskIcon(string $href, ?string $color = null)
 * @method static \Laravel\Head\HeadManager manifest(string $href = '/site.webmanifest', bool|string|null $crossorigin = null)
 * @method static \Laravel\Head\HeadManager appleTouchStartupImage(string $href, ?string $media = null)
 * @method static \Laravel\Head\HeadManager pwa(string $name, string $manifest = '/site.webmanifest', ?string $themeColor = null, ?string $appleTouchIcon = null, ?string $appleTouchIconSizes = '180x180', ?string $appleWebAppStatusBarStyle = null)
 * @method static \Laravel\Head\HeadManager schema(\Laravel\Head\Schema\SchemaObject|array<string, mixed>|callable $schema)
 * @method static array<string, mixed> toArray(?int $status = null)
 * @method static array<int, string> toElements(?int $status = null)
 * @method static array<int, string> toInertiaElements(?int $status = null)
 * @method static \Illuminate\Support\HtmlString render(?int $status = null)
 * @method static \Illuminate\Support\HtmlString renderForView(array<string, mixed> $variables = [], ?int $status = null)
 * @method static string toHtml(?int $status = null)
 * @method static \Laravel\Head\HeadManager flush()
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
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'head';
    }
}
