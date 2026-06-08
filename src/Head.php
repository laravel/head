<?php

declare(strict_types=1);

namespace Laravel\Head;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Laravel\Head\Routing\HeadDefinition;
use Laravel\Head\Routing\RouteHeadRepository;
use Laravel\Head\Schema as SchemaFactory;
use Laravel\Head\Schema\SchemaObject;

class Head
{
    protected HeadData $defaults;

    protected Errors $errors;

    protected ?HeadData $recording = null;

    public function __construct(
        protected Container $app,
        protected HeadRenderer $renderer,
        protected RouteHeadRepository $routes,
    ) {
        $this->defaults = new HeadData;
        $this->errors = new Errors;
    }

    /**
     * Register global head defaults.
     */
    public function defaults(callable $callback): static
    {
        $this->record($callback, function (HeadData $data): void {
            $this->defaults = $this->defaults->merge($data->asDefaults());
        });

        return $this;
    }

    /**
     * Register status-code specific error head definitions.
     */
    public function errors(callable $callback): static
    {
        $callback($this->errors);

        return $this;
    }

    /**
     * Mark the current request as rendering an error response.
     */
    public function status(int $status): static
    {
        $this->current()->status($status);

        return $this;
    }

    public function title(string $title, ?string $prefix = null, ?string $suffix = null, ?bool $bare = null): static
    {
        $this->data()->title($title, prefix: $prefix, suffix: $suffix, bare: $bare);

        return $this;
    }

    /**
     * Set the page description.
     */
    public function description(string $description): static
    {
        $this->data()->description($description);

        return $this;
    }

    public function canonical(string|false|null $url = null, ?bool $forceHttps = null, ?bool $trailingSlash = null): static
    {
        $this->data()->canonical($url, forceHttps: $forceHttps, trailingSlash: $trailingSlash);

        return $this;
    }

    public function robots(string $directives): static
    {
        $this->data()->robots($directives);

        return $this;
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
        ?string $type = null,
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

    /**
     * Add a preload performance hint.
     */
    public function preload(string $href, ?string $as = null, bool|string|null $crossorigin = null, ?string $type = null, ?string $media = null): static
    {
        $this->data()->preload($href, as: $as, crossorigin: $crossorigin, type: $type, media: $media);

        return $this;
    }

    /**
     * Add a prefetch performance hint.
     */
    public function prefetch(string $href, ?string $as = null): static
    {
        $this->data()->prefetch($href, as: $as);

        return $this;
    }

    /**
     * Add a preconnect performance hint.
     */
    public function preconnect(string $href, bool|string|null $crossorigin = null): static
    {
        $this->data()->preconnect($href, crossorigin: $crossorigin);

        return $this;
    }

    /**
     * Add a DNS prefetch performance hint.
     */
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

    /**
     * Add an RSS or Atom feed discovery link.
     */
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

    public function meta(string $key, string $content, ?bool $property = null): static
    {
        $this->data()->meta($key, $content, $property);

        return $this;
    }

    /**
     * @param  array<string, bool|float|int|string|null>  $attributes
     */
    public function link(string $rel, string $href, array $attributes = []): static
    {
        $this->data()->link($rel, $href, $attributes);

        return $this;
    }

    /**
     * Resolve the current request head data into a serializable payload.
     *
     * @return array<string, mixed>
     */
    public function toArray(?int $status = null): array
    {
        return $this->renderer->toArray($this->resolve($status), $this->request());
    }

    /**
     * Render the current head tags.
     */
    public function render(?int $status = null): string
    {
        return $this->renderer->render($this->resolve($status), $this->request());
    }

    /**
     * Flush controller-level data for the current request scope.
     */
    public function flush(): static
    {
        $this->current()->flush();

        return $this;
    }

    protected function resolve(?int $status = null): HeadData
    {
        $data = HeadData::base()->merge($this->defaults);

        if ($route = $this->route()) {
            foreach ($this->routes->groups($route) as $definition) {
                $data = HeadDefinition::apply($data, $definition, $route);
            }

            $definition = $this->routes->get($route);

            if (! is_null($definition)) {
                $data = HeadDefinition::apply($data, $definition, $route);
            }
        }

        $data = $data->merge($this->current()->data());

        $errorStatus = $status ?? $this->current()->status();

        if (! is_null($errorStatus) && $error = $this->errors->forStatus($errorStatus)) {
            $data = $data->merge($error);
        }

        return $data;
    }

    protected function data(): HeadData
    {
        return $this->recording ?? $this->current()->data();
    }

    protected function current(): CurrentHead
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
