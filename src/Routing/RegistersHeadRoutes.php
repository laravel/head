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
    public function __construct(protected RouteHeadRepository $repository)
    {
        //
    }

    public function register(): void
    {
        Route::macro('withHead', function (...$head): Route {
            app(RouteHeadRepository::class)->put($this, RouteAttributeParser::arguments($head));

            return $this;
        });

        $routes = $this;

        RouteRegistrar::macro('withHead', function (...$head) use ($routes): RouteRegistrar {
            return $routes->withGroupHead($this, RouteAttributeParser::arguments($head));
        });

        Router::macro('withHead', function (...$head) use ($routes): RouteRegistrar {
            return $routes->withGroupHead(new RouteRegistrar($this), RouteAttributeParser::arguments($head));
        });

        PendingResourceRegistration::macro('withHead', function (...$head) use ($routes): PendingResourceRegistration {
            return $routes->withPendingHead($this, RouteAttributeParser::arguments($head));
        });

        PendingSingletonResourceRegistration::macro('withHead', function (...$head) use ($routes): PendingSingletonResourceRegistration {
            return $routes->withPendingHead($this, RouteAttributeParser::arguments($head));
        });
    }

    /**
     * @param  array<mixed, mixed>|Closure  $attributes
     */
    public function withGroupHead(RouteRegistrar $registrar, array|Closure $attributes): RouteRegistrar
    {
        $this->repository->pushGroup($registrar, $attributes);

        return $registrar;
    }

    /**
     * @template TRegistration of \Illuminate\Routing\PendingResourceRegistration|\Illuminate\Routing\PendingSingletonResourceRegistration
     *
     * @param  TRegistration  $registration
     * @param  array<mixed, mixed>|Closure  $attributes
     * @return TRegistration
     */
    public function withPendingHead(PendingResourceRegistration|PendingSingletonResourceRegistration $registration, array|Closure $attributes): PendingResourceRegistration|PendingSingletonResourceRegistration
    {
        $this->repository->putPending($registration, $attributes);

        return $registration;
    }
}
