<?php

declare(strict_types=1);

namespace Laravel\Head;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Routing\ResourceRegistrar as BaseResourceRegistrar;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Laravel\Head\Routing\RegistersHeadRoutes;
use Laravel\Head\Routing\ResourceRegistrar;
use Laravel\Head\Routing\RouteHeadRepository;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class HeadServiceProvider extends ServiceProvider
{
    /**
     * Register the package services.
     */
    public function register(): void
    {
        $this->app->scoped(CurrentHead::class);

        $this->app->singleton(Schema::class);
        $this->app->alias(Schema::class, 'head.schema');

        $this->app->singleton(HeadRenderer::class);
        $this->app->singleton(SchemaValidator::class);
        $this->app->singleton(RouteHeadRepository::class);

        $this->app->singleton(Head::class, fn ($app): Head => new Head($app, $app->make(HeadRenderer::class), $app->make(RouteHeadRepository::class)));
        $this->app->alias(Head::class, 'head');

        $this->app->bind(BaseResourceRegistrar::class, fn ($app): ResourceRegistrar => new ResourceRegistrar($app['router'], $app->make(RouteHeadRepository::class)));
    }

    /**
     * Bootstrap the package services.
     */
    public function boot(): void
    {
        Blade::directive('head', fn (): string => "<?php echo app('head')->render(); ?>");

        $this->app->make(RegistersHeadRoutes::class)->register();

        $this->registerExceptionStatusResolver();
        $this->shareWithInertia();
    }

    protected function registerExceptionStatusResolver(): void
    {
        $this->app->afterResolving(ExceptionHandler::class, function (ExceptionHandler $handler): void {
            if (! method_exists($handler, 'renderable')) {
                return;
            }

            $app = $this->app;

            $handler->renderable(function (Throwable $exception, Request $request) use ($app): void {
                $app->make(Head::class)->status(
                    $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500
                );
            });
        });
    }

    protected function shareWithInertia(): void
    {
        if (! class_exists(Inertia::class)) {
            return;
        }

        Inertia::share('head', Inertia::always(
            fn (): array => $this->app->make(Head::class)->toArray(),
        ));
    }
}
