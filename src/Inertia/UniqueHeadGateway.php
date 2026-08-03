<?php

declare(strict_types=1);

namespace Laravel\Head\Inertia;

use Closure;
use Illuminate\Support\Arr;
use Inertia\Ssr\DisablesSsr;
use Inertia\Ssr\ExcludesSsrPaths;
use Inertia\Ssr\Gateway;
use Inertia\Ssr\HasHealthCheck;
use Inertia\Ssr\Response;
use Laravel\Head\HeadManager;
use Laravel\Head\Rendering\HeadRenderer;
use LogicException;

class UniqueHeadGateway implements DisablesSsr, ExcludesSsrPaths, Gateway, HasHealthCheck
{
    public function __construct(
        protected Gateway $gateway,
        protected HeadManager $head,
        protected HeadRenderer $renderer,
    ) {}

    /**
     * Dispatch the page to the SSR engine, stripping the page-managed head
     * elements from the response while preserving any other elements
     * produced by JavaScript SSR.
     *
     * @param  array<string, mixed>  $page
     */
    public function dispatch(array $page): ?Response
    {
        $response = $this->gateway->dispatch($page);

        if (is_null($response)) {
            return null;
        }

        /** @var array<int, string> $elements */
        $elements = Arr::get($page, 'props.'.$this->head->inertiaProp(), []);

        $response->head = $this->renderer->withoutInertiaElements($response->head, $elements);

        return $response;
    }

    /**
     * Forward conditional SSR disabling to the decorated gateway.
     */
    public function disable(Closure|bool $condition): void
    {
        if (! $this->gateway instanceof DisablesSsr) {
            throw new LogicException('The configured SSR gateway does not support disabling server-side rendering conditionally.');
        }

        $this->gateway->disable($condition);
    }

    /**
     * Forward SSR path exclusions to the decorated gateway.
     *
     * @param  array<int, string>|string  $paths
     */
    public function except(array|string $paths): void
    {
        if (! $this->gateway instanceof ExcludesSsrPaths) {
            throw new LogicException('The configured SSR gateway does not support excluding paths from server-side rendering.');
        }

        $this->gateway->except($paths);
    }

    /**
     * Forward SSR health checks to the decorated gateway.
     */
    public function isHealthy(): bool
    {
        if (! $this->gateway instanceof HasHealthCheck) {
            return false;
        }

        return $this->gateway->isHealthy();
    }
}
