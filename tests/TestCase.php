<?php

declare(strict_types=1);

namespace Laravel\Head\Tests;

use Laravel\Head\HeadServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            HeadServiceProvider::class,
        ];
    }
}
