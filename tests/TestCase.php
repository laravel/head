<?php

declare(strict_types=1);

namespace Laravel\Head\Tests;

use Inertia\ServiceProvider;
use Laravel\Head\HeadServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function defineEnvironment($app): void
    {
        $app['view']->addLocation(__DIR__.'/Fixtures/views');
    }

    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
            LivewireServiceProvider::class,
            HeadServiceProvider::class,
        ];
    }
}
