<?php

declare(strict_types=1);

use Laravel\Head\Enums\ImageType;
use Laravel\Head\Enums\Media;
use Laravel\Head\Facades\Head;

it('renders performance links', function (): void {
    Head::preload('/fonts/inter.woff2', as: 'font', crossorigin: true)
        ->preload('/images/hero.webp', as: 'image', type: ImageType::Webp)
        ->preload('/images/hero-dark.webp', as: 'image', type: ImageType::Webp, media: Media::Dark)
        ->prefetch('/images/next.webp')
        ->preconnect('https://fonts.example.com')
        ->dnsPrefetch('https://analytics.example.com');

    expect(Head::toHtml())
        ->toContain('rel="preload" href="/fonts/inter.woff2" as="font" crossorigin>')
        ->toContain('rel="preload" href="/images/hero.webp" as="image" type="image/webp">')
        ->toContain('rel="preload" href="/images/hero-dark.webp" as="image" type="image/webp" media="(prefers-color-scheme: dark)">')
        ->toContain('rel="prefetch" href="/images/next.webp">')
        ->toContain('rel="preconnect" href="https://fonts.example.com">')
        ->toContain('rel="dns-prefetch" href="https://analytics.example.com">');
});

it('detects the as attribute for preloaded and prefetched assets', function (): void {
    Head::preloadAsset('fonts/inter.woff2')
        ->preloadAsset('images/hero.webp?v=2', media: Media::Dark)
        ->preloadAsset('css/app.css')
        ->prefetchAsset('js/checkout.js');

    expect(Head::toHtml())
        ->toContain('rel="preload" href="http://localhost/fonts/inter.woff2" as="font" crossorigin>')
        ->toContain('rel="preload" href="http://localhost/images/hero.webp?v=2" as="image" media="(prefers-color-scheme: dark)">')
        ->toContain('rel="preload" href="http://localhost/css/app.css" as="style">')
        ->toContain('rel="prefetch" href="http://localhost/js/checkout.js" as="script">');
});

it('lets an explicit as attribute override asset detection', function (): void {
    Head::preloadAsset('fonts/inter.woff2', as: 'fetch');

    expect(Head::toHtml())
        ->toContain('as="fetch"')
        ->not->toContain('crossorigin');
});

it('throws when a preloaded asset as attribute cannot be detected', function (): void {
    Head::preloadAsset('downloads/report.xyz');
})->throws(
    InvalidArgumentException::class,
    'Unable to detect a preload [as] attribute for asset [downloads/report.xyz]. Pass one explicitly.',
);

it('omits the as attribute for prefetched assets it cannot detect', function (): void {
    Head::prefetchAsset('downloads/report.xyz');

    expect(Head::toHtml())
        ->toContain('rel="prefetch" href="http://localhost/downloads/report.xyz">');
});
