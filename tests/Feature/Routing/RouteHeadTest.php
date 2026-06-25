<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Laravel\Head\Enums\ImageType;
use Laravel\Head\Enums\OgType;
use Laravel\Head\Enums\TwitterCard;
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadManager;

it('cascades defaults route groups routes and controller mutations', function (): void {
    Head::defaults(function (HeadManager $head): void {
        $head
            ->title('Acme', suffix: ' - Acme')
            ->description('Default description.');
    });

    Route::withHead(description: 'Admin description.', robots: 'noindex, nofollow')
        ->prefix('admin')
        ->group(function (): void {
            Route::get('/dashboard', function (): string {
                Head::title('Runtime dashboard');

                return Head::toHtml();
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
            Route::get('/dashboard', fn (): string => Head::toHtml())
                ->withHead(title: 'Dashboard', og: ['title' => 'Dashboard']);
        });

    $this->get('/admin/reports/dashboard')
        ->assertOk()
        ->assertSee('<title>Dashboard</title>', false)
        ->assertSee('<meta name="description" content="Admin reports">', false)
        ->assertSee('<meta property="og:site_name" content="Acme">', false)
        ->assertSee('<meta property="og:title" content="Dashboard">', false);
});

it('stores head attributes on view and controller routes', function (): void {
    $view = Route::view('/contact', 'contact')->withHead(
        title: 'Contact',
        description: 'Get in touch.',
    );

    $controller = Route::get('/about', fn (): string => 'About')->withHead(
        title: 'About',
    );

    expect($view->getMetadata('head'))->toBe([
        'title' => 'Contact',
        'description' => 'Get in touch.',
    ])->and($controller->getMetadata('head'))->toBe([
        'title' => 'About',
    ]);
});

it('resolves route head closures at request time', function (): void {
    Route::get('/posts/{post}', fn (): string => Head::toHtml())
        ->withHead(fn (Illuminate\Routing\Route $route): array => [
            'title' => 'Post '.$route->parameter('post'),
        ]);

    $this->get('/posts/laravel-head')
        ->assertOk()
        ->assertSee('<title>Post laravel-head</title>', false);
});

it('stores head attributes on resource and singleton routes', function (): void {
    Route::resource('posts', 'PostController')->withHead(
        robots: 'index, follow',
    );

    Route::singleton('profile', 'ProfileController')->withHead(
        title: 'Your Profile',
    );

    $posts = Route::getRoutes()->getByName('posts.index');
    $profile = Route::getRoutes()->getByName('profile.show');

    expect($posts?->getMetadata('head'))->toBe([
        'robots' => 'index, follow',
    ])->and($profile?->getMetadata('head'))->toBe([
        'title' => 'Your Profile',
    ]);
});

it('parses route head data using the fluent field shapes', function (): void {
    Route::get('/product', fn (): string => Head::toHtml())->withHead(
        title: ['value' => 'Product', 'suffix' => ' - Store'],
        canonical: ['auto' => true, 'forceHttps' => false],
        og: ['type' => OgType::Website, 'image' => 'https://example.com/og.jpg'],
        ogImage: [
            ['url' => 'https://example.com/structured.jpg', 'alt' => 'Structured image', 'width' => 1200, 'type' => ImageType::Jpeg],
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
        ->assertSee('property="og:image" content="https://example.com/og.jpg">', false)
        ->assertSee('property="og:image:alt" content="Structured image">', false)
        ->assertSee('property="og:image:type" content="image/jpeg">', false)
        ->assertSee('property="og:image:width" content="1200">', false)
        ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
        ->assertSee('<meta name="twitter:image:alt" content="Twitter image">', false)
        ->assertSee('<meta property="product:price:amount" content="99.00">', false)
        ->assertSee('rel="manifest" href="/manifest.json">', false);
});

it('does not accept snake case route head data aliases', function (): void {
    Route::get('/legacy-inputs', fn (): string => Head::toHtml())->withHead(
        canonical: ['auto' => true, 'force_https' => false],
        og: ['site_name' => 'Legacy'],
        ogImage: [
            ['url' => 'https://example.com/image.jpg', 'secure_url' => 'https://secure.example.com/image.jpg'],
        ],
    );

    $this->get('/legacy-inputs')
        ->assertOk()
        ->assertSee('<link rel="canonical" href="https://localhost/legacy-inputs">', false)
        ->assertSee('property="og:image" content="https://example.com/image.jpg">', false)
        ->assertDontSee('<meta property="og:site_name" content="Legacy">', false)
        ->assertDontSee('property="og:image:secure_url" content="https://secure.example.com/image.jpg">', false);
});

it('throws for unknown route head data keys', function (): void {
    Route::get('/unknown-head-key', fn (): string => Head::toHtml())->withHead(
        heading: 'Dashboard',
    );

    $this->withoutExceptionHandling()->get('/unknown-head-key');
})->throws(InvalidArgumentException::class, 'Unknown route head attribute [heading].');

it('throws for positional route head data values', function (): void {
    Route::get('/positional-head-key', fn (): string => Head::toHtml())->withHead('Dashboard');

    $this->withoutExceptionHandling()->get('/positional-head-key');
})->throws(InvalidArgumentException::class, 'Route head attributes must be named.');
