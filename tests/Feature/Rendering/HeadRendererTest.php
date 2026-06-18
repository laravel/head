<?php

declare(strict_types=1);

use Illuminate\Pagination\LengthAwarePaginator;
use Laravel\Head\Facades\Head;
use Laravel\Head\Facades\Schema;
use Laravel\Head\HeadManager;
use Laravel\Head\OgType;
use Laravel\Head\TwitterCard;

it('renders resolved head metadata in section order', function (): void {
    Head::defaults(function (HeadManager $head): void {
        $head
            ->title('Acme', suffix: ' - Acme')
            ->description('Build something great.')
            ->canonical()
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

    $html = Head::toHtml();

    expect($html)
        ->toContain('<title>About - Acme</title>')
        ->toContain('<meta name="description" content="About Acme.">')
        ->toContain('<link rel="canonical" href="https://example.com/about">')
        ->toContain('<meta property="og:site_name" content="Acme">')
        ->toContain('<meta name="twitter:card" content="summary_large_image">')
        ->toContain('rel="preload" href="/fonts/inter.woff2" as="font" crossorigin>')
        ->toContain('rel="prefetch" href="/images/next.webp">')
        ->toContain('rel="preconnect" href="https://fonts.example.com">')
        ->toContain('rel="dns-prefetch" href="https://analytics.example.com">')
        ->toContain('rel="prev" href="/posts?page=1">')
        ->toContain('rel="next" href="/posts?page=3">')
        ->toContain('rel="alternate" hreflang="fr" href="https://example.com/fr/about">')
        ->toContain('rel="alternate" type="application/rss+xml" title="Acme RSS" href="/feed">')
        ->toContain('"@type":"WebPage"');
});

it('does not render inertia keys in blade html output', function (): void {
    Head::title('Dashboard')
        ->description('Dashboard overview.');

    expect(Head::toHtml())
        ->toContain('<title>Dashboard</title>')
        ->toContain('<meta name="description" content="Dashboard overview.">')
        ->not->toContain('data-inertia');
});

it('groups link sections under the links key when serialized to an array', function (): void {
    Head::title('About')
        ->preload('/fonts/inter.woff2', as: 'font')
        ->paginate(new LengthAwarePaginator(['post'], total: 30, perPage: 10, currentPage: 2, options: ['path' => '/posts']))
        ->alternates(['fr' => 'https://example.com/fr/about'])
        ->feed('/feed', title: 'Acme RSS')
        ->link('manifest', '/site.webmanifest');

    $array = Head::toArray();

    expect($array['title'])->toBe('About')
        ->and($array['openGraph'])->toBe([])
        ->and(array_keys($array['links']))->toEqualCanonicalizing([
            'performance', 'pagination', 'alternates', 'feeds', 'generic',
        ])
        ->and($array['links']['performance']['preload'][0]['href'])->toBe('/fonts/inter.woff2')
        ->and($array['links']['pagination'])->toBe(['prev' => '/posts?page=1', 'next' => '/posts?page=3'])
        ->and($array['links']['alternates'])->toBe(['fr' => 'https://example.com/fr/about']);
});
