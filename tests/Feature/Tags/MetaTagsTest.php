<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Laravel\Head\Enums\Media;
use Laravel\Head\Facades\Head;

it('renders generic meta tags', function (): void {
    Head::meta('format-detection', 'telephone=no')
        ->meta('article:author', 'Taylor Otwell')
        ->meta('weird:namespace', 'Value', property: true);

    expect(Head::toHtml())
        ->toContain('<meta name="format-detection" content="telephone=no">')
        ->toContain('<meta property="article:author" content="Taylor Otwell">')
        ->toContain('<meta property="weird:namespace" content="Value">');
});

it('renders meta aliases', function (): void {
    Head::applicationName('Acme')
        ->colorScheme('light dark')
        ->referrer('strict-origin-when-cross-origin')
        ->viewport('width=device-width, initial-scale=1')
        ->appleWebAppTitle('Acme')
        ->webAppCapable()
        ->appleWebAppStatusBarStyle('black');

    expect(Head::toHtml())
        ->toContain('<meta name="application-name" content="Acme">')
        ->toContain('<meta name="color-scheme" content="light dark">')
        ->toContain('<meta name="referrer" content="strict-origin-when-cross-origin">')
        ->toContain('<meta name="viewport" content="width=device-width, initial-scale=1">')
        ->toContain('<meta name="apple-mobile-web-app-title" content="Acme">')
        ->toContain('<meta name="mobile-web-app-capable" content="yes">')
        ->toContain('<meta name="apple-mobile-web-app-status-bar-style" content="black">');
});

it('renders media-specific meta tags', function (): void {
    Head::meta('theme-color', '#ffffff', media: '(prefers-color-scheme: light)')
        ->meta('theme-color', '#111827', media: '(prefers-color-scheme: dark)');

    expect(Head::toHtml())
        ->toContain('<meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">')
        ->toContain('<meta name="theme-color" content="#111827" media="(prefers-color-scheme: dark)">');
});

it('overlays meta tags by key property and media query', function (): void {
    Head::meta('theme-color', '#ffffff')
        ->meta('theme-color', '#111827', media: '(prefers-color-scheme: dark)')
        ->meta('theme-color', '#f8fafc');

    expect(Head::toHtml())
        ->toContain('<meta name="theme-color" content="#f8fafc">')
        ->toContain('<meta name="theme-color" content="#111827" media="(prefers-color-scheme: dark)">')
        ->not->toContain('<meta name="theme-color" content="#ffffff">');
});

it('serializes media-specific meta tags to the head array', function (): void {
    Head::meta('theme-color', '#ffffff')
        ->meta('theme-color', '#111827', media: '(prefers-color-scheme: dark)');

    expect(Head::toArray()['meta'])->toBe([
        ['key' => 'theme-color', 'content' => '#ffffff'],
        ['key' => 'theme-color', 'content' => '#111827', 'media' => '(prefers-color-scheme: dark)'],
    ]);
});

it('resolves media-specific meta tags from route attributes', function (): void {
    Route::get('/dashboard', fn (): string => Head::toHtml())->withHead(
        meta: [
            ['key' => 'theme-color', 'content' => '#ffffff', 'media' => '(prefers-color-scheme: light)'],
            ['key' => 'theme-color', 'content' => '#111827', 'media' => '(prefers-color-scheme: dark)'],
        ],
    );

    $this->get('/dashboard')
        ->assertOk()
        ->assertSee('<meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">', false)
        ->assertSee('<meta name="theme-color" content="#111827" media="(prefers-color-scheme: dark)">', false);
});

it('resolves media enum values from route attributes', function (): void {
    Route::get('/dashboard', fn (): string => Head::toHtml())->withHead(
        meta: [
            ['key' => 'theme-color', 'content' => '#ffffff', 'media' => Media::Light],
            ['key' => 'theme-color', 'content' => '#111827', 'media' => Media::Dark],
        ],
    );

    $this->get('/dashboard')
        ->assertOk()
        ->assertSee('<meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">', false)
        ->assertSee('<meta name="theme-color" content="#111827" media="(prefers-color-scheme: dark)">', false);
});

it('resolves meta aliases from route attributes', function (): void {
    Route::get('/settings', fn (): string => Head::toHtml())->withHead(
        applicationName: 'Acme',
        colorScheme: 'light dark',
        referrer: 'strict-origin',
        viewport: 'width=device-width, initial-scale=1',
        appleWebAppTitle: 'Acme',
        webAppCapable: true,
        appleWebAppStatusBarStyle: 'black-translucent',
    );

    $this->get('/settings')
        ->assertOk()
        ->assertSee('<meta name="application-name" content="Acme">', false)
        ->assertSee('<meta name="color-scheme" content="light dark">', false)
        ->assertSee('<meta name="referrer" content="strict-origin">', false)
        ->assertSee('<meta name="viewport" content="width=device-width, initial-scale=1">', false)
        ->assertSee('<meta name="apple-mobile-web-app-title" content="Acme">', false)
        ->assertSee('<meta name="mobile-web-app-capable" content="yes">', false)
        ->assertSee('<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">', false);
});
