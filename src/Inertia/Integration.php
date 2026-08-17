<?php

declare(strict_types=1);

namespace Laravel\Head\Inertia;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel;
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
        $this->registerMiddleware();
    }

    /**
     * Share head elements during every request so integrations such as Octane
     * can safely flush Inertia's shared props between operations.
     */
    protected function registerMiddleware(): void
    {
        $push = static function (object $kernel): void {
            if (method_exists($kernel, 'pushMiddleware')) {
                $kernel->pushMiddleware(ShareHead::class);
            }
        };

        $this->app->afterResolving(Kernel::class, $push);

        if ($this->app->resolved(Kernel::class)) {
            $push($this->app->make(Kernel::class));
        }
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
