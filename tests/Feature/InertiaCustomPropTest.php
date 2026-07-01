<?php

declare(strict_types=1);

namespace Laravel\Head\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Support\Header;
use Laravel\Head\Facades\Head;
use Laravel\Head\Tests\TestCase;

class InertiaCustomPropTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app->booting(fn () => Head::inertia(prop: '_head'));
    }

    public function test_head_elements_are_shared_under_the_configured_prop(): void
    {
        Route::get('/dashboard', fn () => Inertia::render('Dashboard', [
            'head' => 'An unrelated app prop',
        ]))->metadata(Head::route(title: 'Dashboard'));

        $this->get('/dashboard', [Header::INERTIA => 'true'])
            ->assertOk()
            ->assertJsonPath('props.head', 'An unrelated app prop')
            ->assertJsonPath('props._head.0', '<title data-inertia="title">Dashboard</title>');
    }
}
