<?php

declare(strict_types=1);

namespace Laravel\Head;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
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
    }

    /**
     * Bootstrap the package services.
     */
    public function boot(): void
    {
        Blade::directive('head', fn (string $expression): string => $expression === ''
            ? "<?php echo app('head')->renderForView(get_defined_vars()); ?>"
            : "<?php echo app('head')->renderForView(get_defined_vars(), {$expression}); ?>"
        );

        (new HeadRouteMacros)->register();

        $this->registerExceptionStatusResolver();
        $this->shareWithInertia();
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

    protected function shareWithInertia(): void
    {
        if (! class_exists(Inertia::class)) {
            return;
        }

        // A plain callable (rather than Inertia::always) keeps the elements
        // off the wire during partial reloads; the client retains the head
        // from the last full visit.
        $this->app->booted(function (): void {
            $head = $this->app->make(HeadManager::class);

            Inertia::share(
                $head->inertiaProp(),
                fn (): array => $this->app->make(HeadManager::class)->toInertiaElements(),
            );
        });
    }
}
