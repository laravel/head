<?php

declare(strict_types=1);

namespace Laravel\Head\Routing;

use Closure;
use Illuminate\Routing\PendingResourceRegistration;
use Illuminate\Routing\PendingSingletonResourceRegistration;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Routing\RouteRegistrar;
use ReflectionProperty;

class RegistersHeadRoutes
{
    public function register(): void
    {
        Route::macro('head', function (...$head): Route {
            $this->action[HeadDefinition::HEAD] = HeadDefinition::arguments($head);

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
        $attributes = $this->arrayProperty($registrar, 'attributes');
        $groups = $attributes[HeadDefinition::GROUPS] ?? [];
        $groups = is_array($groups) ? $groups : [];
        $groups[] = $definition;
        $attributes[HeadDefinition::GROUPS] = $groups;

        $this->setProperty($registrar, 'attributes', $attributes);

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
        $options = $this->arrayProperty($registration, 'options');
        $options[HeadDefinition::HEAD] = $definition;

        $this->setProperty($registration, 'options', $options);

        return $registration;
    }

    /**
     * @return array<string, mixed>
     */
    protected function arrayProperty(object $target, string $property): array
    {
        $value = (new ReflectionProperty($target, $property))->getValue($target);

        return is_array($value) ? $value : [];
    }

    /**
     * @param  array<string, mixed>  $value
     */
    protected function setProperty(object $target, string $property, array $value): void
    {
        (new ReflectionProperty($target, $property))->setValue($target, $value);
    }
}
