<?php

declare(strict_types=1);

use Illuminate\Pagination\LengthAwarePaginator;
use Laravel\Head\CanonicalMode;
use Laravel\Head\Facades\Head;
use Laravel\Head\Facades\Schema;
use Laravel\Head\OgType;
use Laravel\Head\TwitterCard;

it('renders metadata and performance links', function (): void {
    Head::defaults(function (Laravel\Head\Head $head): void {
        $head
            ->title('Acme', suffix: ' - Acme')
            ->description('Build something great.')
            ->canonical(CanonicalMode::Auto)
            ->og(siteName: 'Acme', type: OgType::Website)
            ->twitter(card: TwitterCard::SummaryLargeImage)
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

it('renders the default title without applying its inherited suffix', function (): void {
    Head::defaults(fn (Laravel\Head\Head $head) => $head->title('Acme', suffix: ' - Acme'));

    expect(Head::render())->toContain('<title>Acme</title>');
});

it('can render a bare title without the configured suffix', function (): void {
    Head::defaults(fn (Laravel\Head\Head $head) => $head->title('Acme', suffix: ' - Acme'));

    Head::title('Checkout', bare: true);

    expect(Head::render())->toContain('<title>Checkout</title>');
});

it('renders open graph and twitter media from top level methods', function (): void {
    Head::title('Gallery')
        ->description('Gallery description.')
        ->og(type: OgType::Article, image: 'https://example.com/hero.jpg')
        ->ogImage(
            'https://example.com/gallery.jpg',
            alt: 'Gallery image',
            width: 1200,
            height: 630,
            type: 'image/jpeg',
            secureUrl: 'https://secure.example.com/gallery.jpg',
        )
        ->twitter(card: TwitterCard::SummaryLargeImage, image: 'https://example.com/twitter.jpg')
        ->twitterImage('https://example.com/twitter-alt.jpg', alt: 'Twitter alt');

    expect(Head::render())
        ->toContain('<meta property="og:type" content="article">')
        ->toContain('<meta property="og:image" content="https://example.com/hero.jpg">')
        ->toContain('<meta property="og:image" content="https://example.com/gallery.jpg">')
        ->toContain('<meta property="og:image:secure_url" content="https://secure.example.com/gallery.jpg">')
        ->toContain('<meta property="og:image:type" content="image/jpeg">')
        ->toContain('<meta property="og:image:width" content="1200">')
        ->toContain('<meta property="og:image:height" content="630">')
        ->toContain('<meta property="og:image:alt" content="Gallery image">')
        ->toContain('<meta name="twitter:card" content="summary_large_image">')
        ->toContain('<meta name="twitter:image" content="https://example.com/twitter-alt.jpg">')
        ->toContain('<meta name="twitter:image:alt" content="Twitter alt">');
});

it('renders generic meta and link tags', function (): void {
    Head::meta('theme-color', '#000000')
        ->meta('article:author', 'Taylor Otwell')
        ->meta('weird:namespace', 'Value', property: true)
        ->link('manifest', '/manifest.json')
        ->link('apple-touch-icon', '/apple-touch-icon.png', ['sizes' => '180x180']);

    expect(Head::render())
        ->toContain('<meta name="theme-color" content="#000000">')
        ->toContain('<meta property="article:author" content="Taylor Otwell">')
        ->toContain('<meta property="weird:namespace" content="Value">')
        ->toContain('<link rel="manifest" href="/manifest.json">')
        ->toContain('<link rel="apple-touch-icon" href="/apple-touch-icon.png" sizes="180x180">');
});
