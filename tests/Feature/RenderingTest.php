<?php

declare(strict_types=1);

use Illuminate\Pagination\LengthAwarePaginator;
use Laravel\Head\Facades\Head;
use Laravel\Head\Facades\Schema;

it('renders metadata and performance links', function (): void {
    Head::defaults(function (Laravel\Head\Head $head): void {
        $head
            ->title()->suffix(' - Acme')->fallback('Acme')
            ->description('Build something great.')
            ->canonical()->auto()
            ->og()->siteName('Acme')->type('website')
            ->twitter()->card('summary_large_image')
            ->preconnect('https://fonts.example.com');
    });

    Head::title('About')
        ->description('About Acme.')
        ->canonical('http://example.com/about/')
        ->preload('/fonts/inter.woff2', as: 'font', crossorigin: true)
        ->prefetch('/images/next.webp')
        ->dnsPrefetch('https://analytics.example.com')
        ->paginate(new LengthAwarePaginator(['post'], total: 30, perPage: 10, currentPage: 2, options: ['path' => '/posts']))
        ->alternates(['en' => 'https://example.com/about', 'fr' => 'https://example.com/fr/about'])
        ->feed('/feed', title: 'Acme RSS')
        ->schema(Schema::webPage()->name('About')->url('https://example.com/about'));

    $html = Head::render();

    expect($html)
        ->toContain('<title>About - Acme</title>')
        ->toContain('<meta name="description" content="About Acme.">')
        ->toContain('<link rel="canonical" href="https://example.com/about">')
        ->toContain('<meta property="og:site_name" content="Acme">')
        ->toContain('<meta name="twitter:card" content="summary_large_image">')
        ->toContain('<link rel="preload" href="/fonts/inter.woff2" as="font" crossorigin>')
        ->toContain('<link rel="prefetch" href="/images/next.webp">')
        ->toContain('<link rel="preconnect" href="https://fonts.example.com">')
        ->toContain('<link rel="dns-prefetch" href="https://analytics.example.com">')
        ->toContain('<link rel="prev" href="/posts?page=1">')
        ->toContain('<link rel="next" href="/posts?page=3">')
        ->toContain('<link rel="alternate" hreflang="fr" href="https://example.com/fr/about">')
        ->toContain('<link rel="alternate" type="application/rss+xml" title="Acme RSS" href="/feed">')
        ->toContain('"@type":"WebPage"');
});

it('can render a bare title without the configured suffix', function (): void {
    Head::defaults(fn (Laravel\Head\Head $head) => $head->title()->suffix(' - Acme'));

    Head::title('Checkout')->title()->bare();

    expect(Head::render())->toContain('<title>Checkout</title>');
});
