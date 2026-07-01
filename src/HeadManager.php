<?php

declare(strict_types=1);

namespace Laravel\Head;

use BackedEnum;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Traits\Macroable;
use Laravel\Head\Enums\ImageType;
use Laravel\Head\Enums\OgType;
use Laravel\Head\Enums\RobotsRule;
use Laravel\Head\Enums\TwitterCard;
use Laravel\Head\Rendering\HeadRenderer;
use Laravel\Head\Routing\RouteAttributeParser;
use Laravel\Head\Schema\SchemaFactory;
use Laravel\Head\Schema\SchemaObject;
use Laravel\Head\Tags\TagBuilder;

/**
 * @implements Arrayable<string, mixed>
 */
class HeadManager implements Arrayable, Htmlable
{
    use Macroable;

    public const INERTIA_PROP = 'head';

    protected HeadData $globals;

    protected HeadData $defaults;

    protected string $inertiaProp = self::INERTIA_PROP;

    protected ErrorPages $errorPages;

    protected ?HeadData $recording = null;

    public function __construct(
        protected Container $app,
        protected HeadRenderer $renderer,
        protected TagRegistry $registry,
    ) {
        $this->globals = new HeadData;
        $this->defaults = new HeadData;
        $this->errorPages = new ErrorPages($this->registry);
    }

    public function globals(callable $callback): static
    {
        $this->record($callback, function (HeadData $data): void {
            $this->globals = $this->globals->merge($data);
        });

        return $this;
    }

    public function defaults(callable $callback): static
    {
        $this->record($callback, function (HeadData $data): void {
            $this->defaults = $this->defaults->merge($data->asDefaults());
        });

        return $this;
    }

    public function errors(callable $callback): static
    {
        $callback($this->errorPages);

        return $this;
    }

    public function inertia(string $prop = self::INERTIA_PROP): static
    {
        $this->inertiaProp = $prop;

        return $this;
    }

    public function inertiaProp(): string
    {
        return $this->inertiaProp;
    }

    /**
     * Register a custom tag builder to render on every head.
     *
     * @param  class-string<TagBuilder>  $builder
     */
    public function extend(string $builder): static
    {
        $this->registry->extend($builder);

        return $this;
    }

    public function status(int $status): static
    {
        $this->state()->setStatus($status);

        return $this;
    }

    /**
     * Use $exact to render the title without inherited prefix or suffix.
     */
    public function title(string $title, ?string $prefix = null, ?string $suffix = null, ?bool $exact = null): static
    {
        $this->data()->title($title, prefix: $prefix, suffix: $suffix, exact: $exact);

        return $this;
    }

    public function description(string $description): static
    {
        $this->data()->description($description);

        return $this;
    }

    public function themeColor(string $color): static
    {
        $this->data()->themeColor($color);

        return $this;
    }

    public function applicationName(string $name): static
    {
        $this->data()->applicationName($name);

        return $this;
    }

    public function colorScheme(string $scheme): static
    {
        $this->data()->colorScheme($scheme);

        return $this;
    }

    public function referrer(string $policy): static
    {
        $this->data()->referrer($policy);

        return $this;
    }

    public function viewport(string $content): static
    {
        $this->data()->viewport($content);

        return $this;
    }

    public function appleWebAppTitle(string $title): static
    {
        $this->data()->appleWebAppTitle($title);

        return $this;
    }

    public function webAppCapable(bool $capable = true): static
    {
        $this->data()->webAppCapable($capable);

        return $this;
    }

    public function appleWebAppStatusBarStyle(string $style): static
    {
        $this->data()->appleWebAppStatusBarStyle($style);

        return $this;
    }

    public function canonical(string|false|null $url = null, ?bool $forceHttps = null, ?bool $trailingSlash = null): static
    {
        $this->data()->canonical($url, forceHttps: $forceHttps, trailingSlash: $trailingSlash);

        return $this;
    }

    /**
     * @param  string|RobotsRule|array<int, string|RobotsRule>  $directives
     */
    public function robots(string|RobotsRule|array $directives): static
    {
        $this->data()->robots($directives);

        return $this;
    }

    public function robotsSearchable(): static
    {
        return $this->robots(RobotsRule::All);
    }

    public function robotsHidden(): static
    {
        return $this->robots(RobotsRule::None);
    }

    public function og(
        OgType|string|null $type = null,
        ?string $title = null,
        ?string $description = null,
        ?string $url = null,
        ?string $image = null,
        ?string $video = null,
        ?string $audio = null,
        ?string $siteName = null,
        ?string $locale = null,
        ?string $determiner = null,
    ): static {
        $this->data()->og(
            type: $type,
            title: $title,
            description: $description,
            url: $url,
            image: $image,
            video: $video,
            audio: $audio,
            siteName: $siteName,
            locale: $locale,
            determiner: $determiner,
        );

        return $this;
    }

    public function ogImage(
        string $url,
        ?string $alt = null,
        ?int $width = null,
        ?int $height = null,
        ImageType|string|null $type = null,
        ?string $secureUrl = null,
    ): static {
        $this->data()->ogImage($url, alt: $alt, width: $width, height: $height, type: $type, secureUrl: $secureUrl);

        return $this;
    }

    public function ogVideo(
        string $url,
        ?string $alt = null,
        ?int $width = null,
        ?int $height = null,
        ?string $type = null,
        ?string $secureUrl = null,
    ): static {
        $this->data()->ogVideo($url, alt: $alt, width: $width, height: $height, type: $type, secureUrl: $secureUrl);

        return $this;
    }

    public function ogAudio(string $url, ?string $type = null, ?string $secureUrl = null): static
    {
        $this->data()->ogAudio($url, type: $type, secureUrl: $secureUrl);

        return $this;
    }

    public function twitter(
        TwitterCard|string|null $card = null,
        ?string $site = null,
        ?string $creator = null,
        ?string $title = null,
        ?string $description = null,
        ?string $image = null,
    ): static {
        $this->data()->twitter(
            card: $card,
            site: $site,
            creator: $creator,
            title: $title,
            description: $description,
            image: $image,
        );

        return $this;
    }

    public function twitterImage(string $url, ?string $alt = null): static
    {
        $this->data()->twitterImage($url, alt: $alt);

        return $this;
    }

    public function preload(string $href, ?string $as = null, bool|string|null $crossorigin = null, ImageType|string|null $type = null, ?string $media = null): static
    {
        $this->data()->preload($href, as: $as, crossorigin: $crossorigin, type: $type, media: $media);

        return $this;
    }

    public function prefetch(string $href, ?string $as = null): static
    {
        $this->data()->prefetch($href, as: $as);

        return $this;
    }

    public function preconnect(string $href, bool|string|null $crossorigin = null): static
    {
        $this->data()->preconnect($href, crossorigin: $crossorigin);

        return $this;
    }

    public function dnsPrefetch(string $href): static
    {
        $this->data()->dnsPrefetch($href);

        return $this;
    }

    /**
     * Add rel=prev/next links from a paginator instance.
     *
     * @param  Paginator<int, mixed>  $paginator
     */
    public function paginate(Paginator $paginator): static
    {
        $this->data()->paginate($paginator);

        return $this;
    }

    /**
     * Add localized alternate URLs.
     *
     * @param  array<string, string>  $alternates
     */
    public function alternates(array $alternates): static
    {
        $this->data()->alternates($alternates);

        return $this;
    }

    public function feed(string $href, string $title, string $type = 'rss'): static
    {
        $this->data()->feed($href, title: $title, type: $type);

        return $this;
    }

    /**
     * Add a JSON-LD schema object to the page.
     *
     * @param  SchemaObject|array<string, mixed>|callable(SchemaFactory): SchemaObject|array<string, mixed>  $schema
     */
    public function schema(SchemaObject|array|callable $schema): static
    {
        if (is_callable($schema)) {
            $schema = $schema($this->app->make(SchemaFactory::class));
        }

        $this->data()->schema($schema);

        return $this;
    }

    public function meta(string $key, string $content, ?bool $property = null, ?string $media = null): static
    {
        $this->data()->meta($key, $content, $property, $media);

        return $this;
    }

    /**
     * @param  array<string, BackedEnum|bool|float|int|string|null>  $attributes
     */
    public function link(string $rel, string $href, array $attributes = []): static
    {
        $this->data()->link($rel, $href, $attributes);

        return $this;
    }

    public function icon(string $href, ImageType|string|null $type = null, ?string $sizes = null, ?string $media = null): static
    {
        $this->data()->icon($href, type: $type, sizes: $sizes, media: $media);

        return $this;
    }

    public function appleTouchIcon(string $href, ?string $sizes = null): static
    {
        $this->data()->appleTouchIcon($href, sizes: $sizes);

        return $this;
    }

    public function maskIcon(string $href, ?string $color = null): static
    {
        $this->data()->maskIcon($href, color: $color);

        return $this;
    }

    public function manifest(string $href = '/site.webmanifest', bool|string|null $crossorigin = null): static
    {
        $this->data()->manifest($href, crossorigin: $crossorigin);

        return $this;
    }

    public function appleTouchStartupImage(string $href, ?string $media = null): static
    {
        $this->data()->appleTouchStartupImage($href, media: $media);

        return $this;
    }

    public function pwa(
        string $name,
        string $manifest = '/site.webmanifest',
        ?string $themeColor = null,
        ?string $appleTouchIcon = null,
        ?string $appleTouchIconSizes = '180x180',
        ?string $appleWebAppStatusBarStyle = null,
    ): static {
        $this->data()->pwa(
            name: $name,
            manifest: $manifest,
            themeColor: $themeColor,
            appleTouchIcon: $appleTouchIcon,
            appleTouchIconSizes: $appleTouchIconSizes,
            appleWebAppStatusBarStyle: $appleWebAppStatusBarStyle,
        );

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(?int $status = null): array
    {
        return $this->renderer->toArray($this->resolveDocument($status), $this->request());
    }

    /**
     * @return array<int, string>
     */
    public function toElements(?int $status = null): array
    {
        return $this->renderer->toElements($this->resolveDocument($status), $this->request());
    }

    /**
     * @return array<int, string>
     */
    public function toInertiaElements(?int $status = null): array
    {
        return $this->renderer->toInertiaElements($this->resolve($status), $this->request());
    }

    public function render(?int $status = null): HtmlString
    {
        return new HtmlString($this->renderer->render($this->resolveDocument($status), $this->request()));
    }

    /**
     * Render tags for a Blade view, adding Inertia ownership attributes when
     * the directive is rendered from an Inertia root view.
     *
     * @param  array<string, mixed>  $variables
     */
    public function renderForView(array $variables = [], ?int $status = null): HtmlString
    {
        if ($this->isInertiaRootView($variables)) {
            return new HtmlString($this->renderer->renderInertiaDocument(
                $this->globals,
                $this->resolve($status),
                $this->request(),
            ));
        }

        return $this->render($status);
    }

    public function toHtml(?int $status = null): string
    {
        return $this->render($status)->toHtml();
    }

    /**
     * Flush runtime data for the current request scope.
     */
    public function flush(): static
    {
        $this->state()->flush();

        return $this;
    }

    /**
     * Resolve all configured head layers into the final data for rendering.
     */
    protected function resolve(?int $status = null): HeadData
    {
        $data = (new HeadData)->merge($this->defaults);

        if ($route = $this->route()) {
            foreach (RouteAttributeParser::routeMetadata($route) as $attributes) {
                $data = RouteAttributeParser::apply($data, $attributes, $this->registry);
            }
        }

        $data = $data->merge($this->state()->data());

        $errorStatus = $status ?? $this->state()->status();

        if (! is_null($errorStatus) && $error = $this->errorPages->forStatus($errorStatus)) {
            $data = $data->merge($error);
        }

        return $data;
    }

    protected function resolveDocument(?int $status = null): HeadData
    {
        return (new HeadData)
            ->merge($this->globals)
            ->merge($this->resolve($status));
    }

    /**
     * @param  array<string, mixed>  $variables
     */
    protected function isInertiaRootView(array $variables): bool
    {
        $page = $variables['page'] ?? null;

        return is_array($page)
            && array_key_exists('component', $page)
            && array_key_exists('props', $page)
            && array_key_exists('url', $page);
    }

    protected function data(): HeadData
    {
        return $this->recording ?? $this->state()->data();
    }

    protected function state(): CurrentHead
    {
        return $this->app->make(CurrentHead::class);
    }

    protected function request(): ?Request
    {
        $request = $this->app->bound('request') ? $this->app->make('request') : null;

        return $request instanceof Request ? $request : null;
    }

    protected function route(): ?Route
    {
        $route = $this->request()?->route();

        return $route instanceof Route ? $route : null;
    }

    protected function record(callable $callback, callable $then): void
    {
        $previous = $this->recording;

        $this->recording = new HeadData;

        try {
            $callback($this);

            $then($this->recording);
        } finally {
            $this->recording = $previous;
        }
    }
}
