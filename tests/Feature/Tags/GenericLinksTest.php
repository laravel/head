<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Laravel\Head\Facades\Head;

it('renders generic link tags', function (): void {
    Head::link('search', '/opensearch.xml', ['type' => 'application/opensearchdescription+xml', 'title' => 'Acme Search'])
        ->link('me', 'https://social.example.com/@acme');

    expect(Head::toHtml())
        ->toContain('rel="search" href="/opensearch.xml" type="application/opensearchdescription+xml" title="Acme Search">')
        ->toContain('rel="me" href="https://social.example.com/@acme">');
});

it('renders link aliases', function (): void {
    Head::icon('/favicon.svg', type: 'image/svg+xml')
        ->icon('/favicon-32x32.png', type: 'image/png', sizes: '32x32')
        ->appleTouchIcon('/apple-touch-icon.png', sizes: '180x180')
        ->maskIcon('/safari-pinned-tab.svg', color: '#111827')
        ->manifest('/site.webmanifest');

    expect(Head::toHtml())
        ->toContain('rel="icon" href="/favicon.svg" type="image/svg+xml">')
        ->toContain('rel="icon" href="/favicon-32x32.png" type="image/png" sizes="32x32">')
        ->toContain('rel="apple-touch-icon" href="/apple-touch-icon.png" sizes="180x180">')
        ->toContain('rel="mask-icon" href="/safari-pinned-tab.svg" color="#111827">')
        ->toContain('rel="manifest" href="/site.webmanifest">');
});

it('resolves link aliases from route attributes', function (): void {
    Route::get('/app', fn (): string => Head::toHtml())->withHead(
        icon: [
            ['href' => '/favicon.svg', 'type' => 'image/svg+xml'],
            ['href' => '/favicon-32x32.png', 'type' => 'image/png', 'sizes' => '32x32'],
        ],
        appleTouchIcon: ['href' => '/apple-touch-icon.png', 'sizes' => '180x180'],
        maskIcon: ['href' => '/safari-pinned-tab.svg', 'color' => '#111827'],
        manifest: '/site.webmanifest',
    );

    $this->get('/app')
        ->assertOk()
        ->assertSee('rel="icon" href="/favicon.svg" type="image/svg+xml">', false)
        ->assertSee('rel="icon" href="/favicon-32x32.png" type="image/png" sizes="32x32">', false)
        ->assertSee('rel="apple-touch-icon" href="/apple-touch-icon.png" sizes="180x180">', false)
        ->assertSee('rel="mask-icon" href="/safari-pinned-tab.svg" color="#111827">', false)
        ->assertSee('rel="manifest" href="/site.webmanifest">', false);
});
