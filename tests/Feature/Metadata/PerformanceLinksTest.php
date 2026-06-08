<?php

declare(strict_types=1);

use Laravel\Head\Facades\Head;

it('renders performance links', function (): void {
    Head::preload('/fonts/inter.woff2', as: 'font', crossorigin: true)
        ->prefetch('/images/next.webp')
        ->preconnect('https://fonts.example.com')
        ->dnsPrefetch('https://analytics.example.com');

    expect(Head::toHtml())
        ->toContain('<link rel="preload" href="/fonts/inter.woff2" as="font" crossorigin>')
        ->toContain('<link rel="prefetch" href="/images/next.webp">')
        ->toContain('<link rel="preconnect" href="https://fonts.example.com">')
        ->toContain('<link rel="dns-prefetch" href="https://analytics.example.com">');
});
