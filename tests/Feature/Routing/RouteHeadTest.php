<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Laravel\Head\Facades\Head;

it('cascades defaults route groups routes and controller mutations', function (): void {
    Head::defaults(function (Laravel\Head\Head $head): void {
        $head
            ->title()->suffix(' - Acme')->fallback('Acme')
            ->description('Default description.');
    });

    Route::withHead(description: 'Admin description.', robots: ['noindex', 'nofollow'])
        ->prefix('admin')
        ->group(function (): void {
            Route::get('/dashboard', function (): string {
                Head::title('Runtime dashboard');

                return Head::render();
            })->head(title: 'Dashboard');
        });

    $this->get('/admin/dashboard')
        ->assertOk()
        ->assertSee('<title>Runtime dashboard - Acme</title>', false)
        ->assertSee('<meta name="description" content="Admin description.">', false)
        ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
});

it('stores head definitions on view and controller routes', function (): void {
    $view = Route::view('/contact', 'contact')->head(
        title: 'Contact',
        description: 'Get in touch.',
    );

    $controller = Route::get('/about', fn (): string => 'About')->head(
        title: 'About',
    );

    expect($view->getAction('laravel_head'))->toBe([
        'title' => 'Contact',
        'description' => 'Get in touch.',
    ])->and($controller->getAction('laravel_head'))->toBe([
        'title' => 'About',
    ]);
});

it('resolves route head closures at request time', function (): void {
    Route::get('/posts/{post}', fn (): string => Head::render())
        ->head(fn (Illuminate\Routing\Route $route): array => [
            'title' => 'Post '.$route->parameter('post'),
        ]);

    $this->get('/posts/laravel-head')
        ->assertOk()
        ->assertSee('<title>Post laravel-head</title>', false);
});
