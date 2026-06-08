<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Laravel\Head\Facades\Head;
use Laravel\Head\OgType;
use Laravel\Head\TwitterCard;

it('cascades defaults route groups routes and controller mutations', function (): void {
    Head::defaults(function (Laravel\Head\Head $head): void {
        $head
            ->title('Acme', suffix: ' - Acme')
            ->description('Default description.');
    });

    Route::withHead(description: 'Admin description.', robots: 'noindex, nofollow')
        ->prefix('admin')
        ->group(function (): void {
            Route::get('/dashboard', function (): string {
                Head::title('Runtime dashboard');

                return Head::render();
            })->withHead(title: 'Dashboard');
        });

    $this->get('/admin/dashboard')
        ->assertOk()
        ->assertSee('<title>Runtime dashboard - Acme</title>', false)
        ->assertSee('<meta name="description" content="Admin description.">', false)
        ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
});

it('keeps route group head closures as a separate cascade layer', function (): void {
    Route::withHead(fn (Illuminate\Routing\Route $route): array => [
        'description' => 'Admin '.$route->parameter('section'),
        'og' => ['siteName' => 'Acme'],
    ])
        ->prefix('admin/{section}')
        ->group(function (): void {
            Route::get('/dashboard', fn (): string => Head::render())
                ->withHead(title: 'Dashboard', og: ['title' => 'Dashboard']);
        });

    $this->get('/admin/reports/dashboard')
        ->assertOk()
        ->assertSee('<title>Dashboard</title>', false)
        ->assertSee('<meta name="description" content="Admin reports">', false)
        ->assertSee('<meta property="og:site_name" content="Acme">', false)
        ->assertSee('<meta property="og:title" content="Dashboard">', false);
});

it('stores head definitions on view and controller routes', function (): void {
    $view = Route::view('/contact', 'contact')->withHead(
        title: 'Contact',
        description: 'Get in touch.',
    );

    $controller = Route::get('/about', fn (): string => 'About')->withHead(
        title: 'About',
    );

    expect($view->getAction('metadata.head'))->toBe([
        'title' => 'Contact',
        'description' => 'Get in touch.',
    ])->and($controller->getAction('metadata.head'))->toBe([
        'title' => 'About',
    ]);
});

it('resolves route head closures at request time', function (): void {
    Route::get('/posts/{post}', fn (): string => Head::render())
        ->withHead(fn (Illuminate\Routing\Route $route): array => [
            'title' => 'Post '.$route->parameter('post'),
        ]);

    $this->get('/posts/laravel-head')
        ->assertOk()
        ->assertSee('<title>Post laravel-head</title>', false);
});

it('stores head definitions on resource and singleton routes', function (): void {
    Route::resource('posts', 'PostController')->withHead(
        robots: 'index, follow',
    );

    Route::singleton('profile', 'ProfileController')->withHead(
        title: 'Your Profile',
    );

    $posts = Route::getRoutes()->getByName('posts.index');
    $profile = Route::getRoutes()->getByName('profile.show');

    expect($posts?->getAction('metadata.head'))->toBe([
        'robots' => 'index, follow',
    ])->and($profile?->getAction('metadata.head'))->toBe([
        'title' => 'Your Profile',
    ]);
});

it('parses route metadata using the fluent field shapes', function (): void {
    Route::get('/product', fn (): string => Head::render())->withHead(
        title: ['value' => 'Product', 'suffix' => ' - Store'],
        canonical: ['auto' => true, 'forceHttps' => false],
        og: ['type' => OgType::Website, 'image' => 'https://example.com/og.jpg'],
        ogImage: [
            ['url' => 'https://example.com/structured.jpg', 'alt' => 'Structured image', 'width' => 1200],
        ],
        twitter: ['card' => TwitterCard::SummaryLargeImage],
        twitterImage: ['url' => 'https://example.com/twitter.jpg', 'alt' => 'Twitter image'],
        meta: ['product:price:amount' => '99.00'],
        link: [
            ['rel' => 'manifest', 'href' => '/manifest.json'],
        ],
    );

    $this->get('/product')
        ->assertOk()
        ->assertSee('<title>Product - Store</title>', false)
        ->assertSee('<link rel="canonical" href="http://localhost/product">', false)
        ->assertSee('<meta property="og:type" content="website">', false)
        ->assertSee('<meta property="og:image" content="https://example.com/og.jpg">', false)
        ->assertSee('<meta property="og:image:alt" content="Structured image">', false)
        ->assertSee('<meta property="og:image:width" content="1200">', false)
        ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
        ->assertSee('<meta name="twitter:image:alt" content="Twitter image">', false)
        ->assertSee('<meta property="product:price:amount" content="99.00">', false)
        ->assertSee('<link rel="manifest" href="/manifest.json">', false);
});

it('does not accept snake case route metadata aliases', function (): void {
    Route::get('/legacy-inputs', fn (): string => Head::render())->withHead(
        canonical: ['auto' => true, 'force_https' => false],
        og: ['site_name' => 'Legacy'],
        ogImage: [
            ['url' => 'https://example.com/image.jpg', 'secure_url' => 'https://secure.example.com/image.jpg'],
        ],
    );

    $this->get('/legacy-inputs')
        ->assertOk()
        ->assertSee('<link rel="canonical" href="https://localhost/legacy-inputs">', false)
        ->assertSee('<meta property="og:image" content="https://example.com/image.jpg">', false)
        ->assertDontSee('<meta property="og:site_name" content="Legacy">', false)
        ->assertDontSee('<meta property="og:image:secure_url" content="https://secure.example.com/image.jpg">', false);
});
