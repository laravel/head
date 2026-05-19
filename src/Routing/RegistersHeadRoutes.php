<?php

declare(strict_types=1);

namespace Laravel\Head\Routing;

use Closure;
use Illuminate\Routing\PendingResourceRegistration;
use Illuminate\Routing\PendingSingletonResourceRegistration;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Routing\RouteRegistrar;

class RegistersHeadRoutes
{
    public function __construct(protected RouteHeadRepository $repository) {}

    public function register(): void
    {
        Route::macro('head', function (...$head): Route {
            app(RouteHeadRepository::class)->put($this, HeadDefinition::arguments($head));

            return $this;
        });

        $routes = $this;

        RouteRegistrar::macro('withHead', function (...$head) use ($routes): RouteRegistrar {
            return $routes->withGroupHead($this, HeadDefinition::arguments($head));
        });

        Router::macro('withHead', function (...$head) use ($routes): RouteRegistrar {
            return $routes->withGroupHead(new RouteRegistrar($this), HeadDefinition::arguments($head));
        });

        PendingResourceRegistration::macro('head', function (...$head) use ($routes): PendingResourceRegistration {
            return $routes->withPendingHead($this, HeadDefinition::arguments($head));
        });

        PendingSingletonResourceRegistration::macro('head', function (...$head) use ($routes): PendingSingletonResourceRegistration {
            return $routes->withPendingHead($this, HeadDefinition::arguments($head));
        });
    }

    /**
     * @param  array<mixed, mixed>|Closure  $definition
     */
    public function withGroupHead(RouteRegistrar $registrar, array|Closure $definition): RouteRegistrar
    {
        $this->repository->pushGroup($registrar, $definition);

        return $registrar;
    }

    /**
     * @template TRegistration of \Illuminate\Routing\PendingResourceRegistration|\Illuminate\Routing\PendingSingletonResourceRegistration
     *
     * @param  TRegistration  $registration
     * @param  array<mixed, mixed>|Closure  $definition
     * @return TRegistration
     */
    public function withPendingHead(PendingResourceRegistration|PendingSingletonResourceRegistration $registration, array|Closure $definition): PendingResourceRegistration|PendingSingletonResourceRegistration
    {
        $this->repository->putPending($registration, $definition);

        return $registration;
    }
}
