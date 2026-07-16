<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Laravel\Head\Enums\ImageType;
use Laravel\Head\Enums\Media;
use Laravel\Head\Facades\Head;

it('renders generic link tags', function (): void {
    Head::link('search', '/opensearch.xml', ['type' => 'application/opensearchdescription+xml', 'title' => 'Acme Search'])
        ->link('me', 'https://social.example.com/@acme')
        ->link('icon', '/favicon.ico', ['type' => ImageType::Ico]);

    expect(Head::toHtml())
        ->toContain('rel="search" href="/opensearch.xml" type="application/opensearchdescription+xml" title="Acme Search">')
        ->toContain('rel="me" href="https://social.example.com/@acme">')
        ->toContain('rel="icon" href="/favicon.ico" type="image/x-icon">');
});

it('renders link aliases', function (): void {
    Head::favicon('/favicon.svg', type: ImageType::Svg)
        ->icon('/favicon-32x32.png', type: ImageType::Png, sizes: '32x32')
        ->appleTouchIcon('/apple-touch-icon.png', sizes: '180x180')
        ->maskIcon('/safari-pinned-tab.svg', color: '#111827')
        ->manifest('/site.webmanifest')
        ->appleTouchStartupImage('/launch.png', media: Media::Portrait);

    expect(Head::toHtml())
        ->toContain('rel="icon" href="/favicon.svg" type="image/svg+xml">')
        ->toContain('rel="icon" href="/favicon-32x32.png" type="image/png" sizes="32x32">')
        ->toContain('rel="apple-touch-icon" href="/apple-touch-icon.png" sizes="180x180">')
        ->toContain('rel="mask-icon" href="/safari-pinned-tab.svg" color="#111827">')
        ->toContain('rel="manifest" href="/site.webmanifest">')
        ->toContain('rel="apple-touch-startup-image" href="/launch.png" media="(orientation: portrait)">');
});

it('resolves link aliases from route attributes', function (): void {
    Route::get('/app', fn (): string => Head::toHtml())->withHead(
        icon: [
            ['href' => '/favicon.svg', 'type' => ImageType::Svg],
            ['href' => '/favicon-32x32.png', 'type' => ImageType::Png, 'sizes' => '32x32'],
        ],
        favicon: ['href' => '/favicon-dark.svg', 'type' => ImageType::Svg, 'media' => Media::Dark],
        appleTouchIcon: ['href' => '/apple-touch-icon.png', 'sizes' => '180x180'],
        maskIcon: ['href' => '/safari-pinned-tab.svg', 'color' => '#111827'],
        manifest: '/site.webmanifest',
        appleTouchStartupImage: ['href' => '/launch.png', 'media' => Media::Portrait],
    );

    $this->get('/app')
        ->assertOk()
        ->assertSee('rel="icon" href="/favicon.svg" type="image/svg+xml">', false)
        ->assertSee('rel="icon" href="/favicon-32x32.png" type="image/png" sizes="32x32">', false)
        ->assertSee('rel="icon" href="/favicon-dark.svg" type="image/svg+xml" media="(prefers-color-scheme: dark)">', false)
        ->assertSee('rel="apple-touch-icon" href="/apple-touch-icon.png" sizes="180x180">', false)
        ->assertSee('rel="mask-icon" href="/safari-pinned-tab.svg" color="#111827">', false)
        ->assertSee('rel="manifest" href="/site.webmanifest">', false)
        ->assertSee('rel="apple-touch-startup-image" href="/launch.png" media="(orientation: portrait)">', false);
});
