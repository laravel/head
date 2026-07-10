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
