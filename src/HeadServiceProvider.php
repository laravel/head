<?php

declare(strict_types=1);

namespace Laravel\Head;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class HeadServiceProvider extends ServiceProvider
{
    /**
     * Register the package services.
     */
    public function register(): void
    {
        $this->app->singleton(Head::class);
        $this->app->alias(Head::class, 'head');
    }

    /**
     * Bootstrap the package services.
     */
    public function boot(): void
    {
        Blade::directive('head', fn (): string => "<?php echo app('head')->render(); ?>");
    }
}
