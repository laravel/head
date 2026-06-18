<?php

declare(strict_types=1);

namespace Laravel\Head;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Routing\ResourceRegistrar as BaseResourceRegistrar;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Laravel\Head\Rendering\HeadRenderer;
use Laravel\Head\Routing\RegistersHeadRoutes;
use Laravel\Head\Routing\ResourceRegistrar;
use Laravel\Head\Routing\RouteHeadRepository;
use Laravel\Head\Schema\SchemaFactory;
use Laravel\Head\Schema\SchemaValidator;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class HeadServiceProvider extends ServiceProvider
{
    /**
     * Register the package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/head.php', 'head');

        $this->app->scoped(CurrentHead::class);

        $this->app->singleton(SchemaFactory::class);
        $this->app->alias(SchemaFactory::class, 'head.schema');

        $this->app->singleton(MetadataRegistry::class);
        $this->app->singleton(HeadRenderer::class);
        $this->app->singleton(SchemaValidator::class);
        $this->app->singleton(RouteHeadRepository::class);

        $this->app->singleton(HeadManager::class, fn ($app): HeadManager => new HeadManager(
            $app,
            $app->make(HeadRenderer::class),
            $app->make(RouteHeadRepository::class),
            $app->make(MetadataRegistry::class),
        ));
        $this->app->alias(HeadManager::class, 'head');

        $this->app->bind(BaseResourceRegistrar::class, fn ($app): ResourceRegistrar => new ResourceRegistrar($app['router'], $app->make(RouteHeadRepository::class)));
    }

    /**
     * Bootstrap the package services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/head.php' => config_path('head.php'),
        ], 'head-config');

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
                $app->make(HeadManager::class)->status(
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

        // A plain callable (rather than Inertia::always) keeps the elements
        // off the wire during partial reloads; the client retains the head
        // from the last full visit.
        Inertia::share(
            Config::string('head.inertia.prop', 'head'),
            fn (): array => $this->app->make(HeadManager::class)->toInertiaElements(),
        );
    }
}
