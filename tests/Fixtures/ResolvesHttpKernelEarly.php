<?php

declare(strict_types=1);

namespace Laravel\Head\Tests\Fixtures;

use Illuminate\Contracts\Http\Kernel;

/**
 * Resolves the HTTP kernel before service providers are registered, mirroring
 * Application::handleRequest(), which resolves the kernel and then bootstraps
 * the application while handling the request.
 */
trait ResolvesHttpKernelEarly
{
    protected function resolveApplicationHttpKernel($app): void
    {
        parent::resolveApplicationHttpKernel($app);

        $app->make(Kernel::class);
    }
}
