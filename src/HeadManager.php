<?php

declare(strict_types=1);

namespace Laravel\Head;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Laravel\Head\Concerns\BuildsHead;
use Laravel\Head\Rendering\HeadRenderer;
use Laravel\Head\Routing\RouteAttributeParser;
use Laravel\Head\Schema\SchemaFactory;
use Laravel\Head\Tags\TagBuilder;

/**
 * @implements Arrayable<string, mixed>
 */
class HeadManager implements Arrayable, Htmlable
{
    use BuildsHead;
    use Conditionable;
    use Macroable;

    public const INERTIA_PROP = 'head';

    protected HeadData $inertiaGlobals;

    protected HeadData $defaults;

    protected string $inertiaProp = self::INERTIA_PROP;

    protected ErrorPages $errorPages;

    public function __construct(
        protected Container $app,
        protected HeadRenderer $renderer,
        protected TagRegistry $registry,
    ) {
        $this->inertiaGlobals = new HeadData;
        $this->defaults = new HeadData;
        $this->errorPages = new ErrorPages(
            $this->registry,
            fn (callable $callback): HeadData => $this->define($callback),
        );
    }

    /**
     * Define head tags rendered as server-owned tags on every Inertia document.
     *
     * @param  callable(HeadBuilder): mixed  $callback
     */
    public function inertiaGlobals(callable $callback): static
    {
        $this->inertiaGlobals = $this->inertiaGlobals->merge($this->define($callback));

        return $this;
    }

    /**
     * Define default head data merged beneath every page's head.
     *
     * @param  callable(HeadBuilder): mixed  $callback
     */
    public function defaults(callable $callback): static
    {
        $this->defaults = $this->defaults->merge($this->define($callback)->asDefaults());

        return $this;
    }

    /**
     * Define head data for error pages.
     *
     * @param  callable(ErrorPages): mixed  $callback
     */
    public function errors(callable $callback): static
    {
        $callback($this->errorPages);

        return $this;
    }

    /**
     * Customize the Inertia prop the head elements are shared under.
     */
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

    /**
     * Set the response status used to resolve error page head data.
     */
    public function status(int $status): static
    {
        $this->state()->setStatus($status);

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
                $this->inertiaGlobals,
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
     * Run a definition callback against a fresh head builder and return its data.
     */
    protected function define(callable $callback): HeadData
    {
        $data = new HeadData;

        $callback(new HeadBuilder($data, $this->schemaFactory()));

        return $data;
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
            ->merge($this->inertiaGlobals)
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

    protected function headData(): HeadData
    {
        return $this->state()->data();
    }

    protected function schemaFactory(): SchemaFactory
    {
        return $this->app->make(SchemaFactory::class);
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
}
