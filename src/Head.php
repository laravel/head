<?php

declare(strict_types=1);

namespace Laravel\Head;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Laravel\Head\Routing\HeadDefinition;
use Laravel\Head\Schema\SchemaObject;

class Head
{
    protected HeadData $defaults;

    protected Errors $errors;

    protected ?HeadData $recording = null;

    public function __construct(
        protected Container $app,
        protected HeadRenderer $renderer,
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
            $this->defaults = $this->defaults->merge($data);
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

    /**
     * Set the page title or configure title rendering.
     */
    public function title(?string $title = null): static|Title
    {
        if (is_null($title)) {
            return new Title($this->data());
        }

        $this->data()->title($title);

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

    /**
     * Set or configure the canonical URL.
     */
    public function canonical(?string $url = null): static|Canonical
    {
        if (is_null($url)) {
            return new Canonical($this->data());
        }

        $this->data()->canonical($url);

        return $this;
    }

    /**
     * Configure Open Graph metadata.
     */
    public function og(): OpenGraph
    {
        return $this->data()->og();
    }

    /**
     * Configure Twitter card metadata.
     */
    public function twitter(): Twitter
    {
        return $this->data()->twitter();
    }

    /**
     * Configure robots metadata.
     */
    public function robots(): Robots
    {
        return $this->data()->robots();
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
     * @param  SchemaObject|array<string, mixed>  $schema
     */
    public function schema(SchemaObject|array $schema): static
    {
        $this->data()->schema($schema);

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
            foreach (HeadDefinition::groups($route) as $definition) {
                HeadDefinition::apply($data, $definition, $route);
            }

            $definition = $route->getAction(HeadDefinition::HEAD);

            if (is_array($definition) || $definition instanceof Closure) {
                HeadDefinition::apply($data, $definition, $route);
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

        $callback($this);

        $then($this->recording);

        $this->recording = $previous;
    }
}
