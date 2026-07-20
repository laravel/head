<?php

declare(strict_types=1);

namespace Laravel\Head;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Laravel\Head\Inertia\Integration as InertiaIntegration;
use Laravel\Head\Rendering\HeadRenderer;
use Laravel\Head\Routing\HeadRouteMacros;
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
        $this->app->scoped(CurrentHead::class);

        $this->app->singleton(SchemaFactory::class);
        $this->app->alias(SchemaFactory::class, 'head.schema');

        $this->app->singleton(TagRegistry::class);
        $this->app->singleton(HeadRenderer::class);
        $this->app->singleton(SchemaValidator::class);

        $this->app->singleton(HeadManager::class, fn ($app): HeadManager => new HeadManager(
            $app,
            $app->make(HeadRenderer::class),
            $app->make(TagRegistry::class),
        ));
        $this->app->alias(HeadManager::class, 'head');

        (new InertiaIntegration($this->app))->register();
    }

    /**
     * Bootstrap the package services.
     */
    public function boot(): void
    {
        Blade::directive('head', fn (string $expression): string => $expression === ''
            ? "<?php echo app('head')->renderForView(); ?>"
            : "<?php echo app('head')->renderForView({$expression}); ?>"
        );

        (new HeadRouteMacros)->register();

        $this->registerExceptionStatusResolver();
    }

    protected function registerExceptionStatusResolver(): void
    {
        $this->app->afterResolving(ExceptionHandler::class, function (object $handler): void {
            $app = $this->app;

            /** @var Handler $handler */
            $handler->renderable(function (Throwable $exception, Request $request) use ($app): void {
                $app->make(HeadManager::class)->status(
                    $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500
                );
            });
        });
    }
}
