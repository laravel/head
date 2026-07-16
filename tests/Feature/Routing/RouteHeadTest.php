<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Laravel\Head\Enums\ImageType;
use Laravel\Head\Enums\OgType;
use Laravel\Head\Enums\TwitterCard;
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadBuilder;
use Laravel\Head\Routing\RouteAttributeParser;

it('cascades defaults route groups routes and controller mutations', function (): void {
    Head::defaults(function (HeadBuilder $head): void {
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

it('keeps route group head metadata as a separate cascade layer', function (): void {
    Route::withHead(
        description: 'Admin reports',
        og: ['siteName' => 'Acme'],
    )
        ->prefix('admin')
        ->group(function (): void {
            Route::get('/dashboard', fn (): string => Head::toHtml())
                ->withHead(title: 'Dashboard', og: ['title' => 'Dashboard']);
        });

    $this->get('/admin/dashboard')
        ->assertOk()
        ->assertSee('<title>Dashboard</title>', false)
        ->assertSee('<meta name="description" content="Admin reports">', false)
        ->assertSee('<meta property="og:site_name" content="Acme">', false)
        ->assertSee('<meta property="og:title" content="Dashboard">', false);
});

it('stores cache friendly head metadata on view and controller routes', function (): void {
    $view = Route::view('/contact', 'contact')->withHead(
        title: 'Contact',
        description: 'Get in touch.',
    );

    $controller = Route::get('/about', fn (): string => 'About')->withHead(
        title: 'About',
    );

    expect(array_values($view->getMetadata(RouteAttributeParser::HEAD)))->toBe([[
        'title' => 'Contact',
        'description' => 'Get in touch.',
    ]])->and(array_values($controller->getMetadata(RouteAttributeParser::HEAD)))->toBe([[
        'title' => 'About',
    ]]);
});

it('stores head metadata on resource and singleton routes', function (): void {
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
        og: ['title' => 'Legacy', 'site_name' => 'Legacy'],
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

it('parses single repeatable route head data values', function (): void {
    Route::get('/assets', fn (): string => Head::toHtml())->withHead(
        preload: ['href' => '/fonts/inter.woff2', 'as' => 'font', 'crossorigin' => true],
        prefetch: '/images/next.webp',
        preconnect: ['href' => 'https://fonts.example.com', 'crossorigin' => true],
        dnsPrefetch: 'https://analytics.example.com',
        feed: ['href' => '/feed.atom', 'title' => 'Acme Atom', 'type' => 'atom'],
    );

    $this->get('/assets')
        ->assertOk()
        ->assertSee('rel="preload" href="/fonts/inter.woff2" as="font" crossorigin>', false)
        ->assertSee('rel="prefetch" href="/images/next.webp">', false)
        ->assertSee('rel="preconnect" href="https://fonts.example.com" crossorigin>', false)
        ->assertSee('rel="dns-prefetch" href="https://analytics.example.com">', false)
        ->assertSee('rel="alternate" type="application/atom+xml" title="Acme Atom" href="/feed.atom">', false);
});

it('throws for unknown route head data keys', function (): void {
    Route::get('/unknown-head-key', fn (): string => Head::toHtml())->metadata([
        RouteAttributeParser::HEAD => [
            'layer' => ['heading' => 'Dashboard'],
        ],
    ]);

    $this->withoutExceptionHandling()->get('/unknown-head-key');
})->throws(InvalidArgumentException::class, 'Unknown route head attribute [heading].');

it('throws for invalid values on known route head data keys', function (): void {
    Route::get('/invalid-head-value', fn (): string => Head::toHtml())->withHead(
        ogImage: ['alt' => 'Missing URL'],
    );

    $this->withoutExceptionHandling()->get('/invalid-head-value');
})->throws(InvalidArgumentException::class, 'Invalid value for route head attribute [ogImage].');

it('throws when route head data tries to suppress canonical URLs', function (): void {
    Route::get('/invalid-canonical', fn (): string => Head::toHtml())->metadata([
        RouteAttributeParser::HEAD => [
            'layer' => ['canonical' => false],
        ],
    ]);

    $this->withoutExceptionHandling()->get('/invalid-canonical');
})->throws(InvalidArgumentException::class, 'Invalid value for route head attribute [canonical].');

it('throws when route canonical data uses the removed none option', function (): void {
    Route::get('/invalid-canonical-none', fn (): string => Head::toHtml())->withHead(
        canonical: ['none' => true],
    );

    $this->withoutExceptionHandling()->get('/invalid-canonical-none');
})->throws(InvalidArgumentException::class, 'Invalid value for route head attribute [canonical].');
