<?php

declare(strict_types=1);

namespace Laravel\Head\Inertia;

use Illuminate\Support\Arr;
use Inertia\Ssr\Gateway;
use Inertia\Ssr\Response;
use Laravel\Head\HeadManager;
use Laravel\Head\Rendering\HeadRenderer;

class UniqueHeadGateway implements Gateway
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
}
