<?php

declare(strict_types=1);

namespace Laravel\Head\Inertia;

use Illuminate\Contracts\Foundation\Application;
use Inertia\Inertia;
use Inertia\Ssr\Gateway;
use Laravel\Head\HeadManager;
use Laravel\Head\Rendering\HeadRenderer;

class Integration
{
    public function __construct(protected Application $app) {}

    /**
     * Register Laravel Head's optional Inertia integration.
     */
    public function register(): void
    {
        if (! class_exists(Inertia::class)) {
            return;
        }

        $this->decorateSsrGateway();

        $this->app->booted(function (): void {
            $head = $this->app->make(HeadManager::class);

            // A plain callable (rather than Inertia::always) keeps the elements
            // off the wire during partial reloads; the client retains the head
            // from the last full visit.
            Inertia::share($head->inertiaProp(), fn (): array => $head->toInertiaElements());
        });
    }

    /**
     * Strip page-managed head elements from Inertia SSR responses at the
     * gateway, so @head remains authoritative for the initial document no
     * matter where it renders relative to Inertia's head component.
     */
    protected function decorateSsrGateway(): void
    {
        $this->app->extend(Gateway::class, fn (Gateway $gateway): Gateway => new UniqueHeadGateway(
            $gateway,
            $this->app->make(HeadManager::class),
            $this->app->make(HeadRenderer::class),
        ));
    }
}
