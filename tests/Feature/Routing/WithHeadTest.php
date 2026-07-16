<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Laravel\Head\Facades\Head;
use Laravel\Head\Routing\RouteAttributeParser;

it('defines head metadata fluently on routes', function (): void {
    $route = Route::view('/contact', 'contact')
        ->name('contact')
        ->withHead(
            title: 'Contact Us',
            description: 'Get in touch.',
        );

    expect($route)->toBeInstanceOf(Illuminate\Routing\Route::class)
        ->and($route->getName())->toBe('contact')
        ->and(array_values($route->getMetadata(RouteAttributeParser::HEAD)))->toBe([[
            'title' => 'Contact Us',
            'description' => 'Get in touch.',
        ]]);
});

it('defines head metadata at the leading group position', function (): void {
    Route::withHead(description: 'Admin description.', robots: 'noindex, nofollow')
        ->prefix('admin')
        ->group(function (): void {
            Route::get('/dashboard', fn (): string => Head::toHtml())
                ->withHead(title: 'Dashboard');
        });

    $this->get('/admin/dashboard')
        ->assertOk()
        ->assertSee('<title>Dashboard</title>', false)
        ->assertSee('<meta name="description" content="Admin description.">', false)
        ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
});

it('defines head metadata after other group attributes', function (): void {
    Route::prefix('admin')
        ->withHead(og: ['siteName' => 'Acme'])
        ->name('admin.')
        ->group(function (): void {
            Route::get('/reports', fn (): string => Head::toHtml())
                ->name('reports')
                ->withHead(title: 'Reports', og: ['title' => 'Reports']);
        });

    $this->get('/admin/reports')
        ->assertOk()
        ->assertSee('<title>Reports</title>', false)
        ->assertSee('<meta property="og:site_name" content="Acme">', false)
        ->assertSee('<meta property="og:title" content="Reports">', false);
});

it('defines head metadata on resource and singleton routes', function (): void {
    Route::resource('posts', 'PostController')->withHead(
        robots: 'index, follow',
    );

    Route::singleton('profile', 'ProfileController')->withHead(
        title: 'Your Profile',
    );

    $posts = Route::getRoutes()->getByName('posts.index');
    $profile = Route::getRoutes()->getByName('profile.show');

    expect(array_values($posts?->getMetadata(RouteAttributeParser::HEAD)))->toBe([[
        'robots' => 'index, follow',
    ]])->and(array_values($profile?->getMetadata(RouteAttributeParser::HEAD)))->toBe([[
        'title' => 'Your Profile',
    ]]);
});

it('stores cache friendly metadata including extension attributes', function (): void {
    $route = Route::get('/article', fn (): string => 'Article')->withHead(
        title: 'Article',
        extensions: ['readingTime' => 4],
    );

    expect(array_values($route->getMetadata(RouteAttributeParser::HEAD)))->toBe([[
        'title' => 'Article',
        'readingTime' => 4,
    ]]);
});

it('rejects unknown named withHead arguments', function (): void {
    $arguments = ['heading' => 'Dashboard'];

    expect(fn () => Route::get('/unknown', fn (): string => 'Unknown')->withHead(...$arguments))
        ->toThrow(Error::class, 'Unknown named parameter $heading');
});
