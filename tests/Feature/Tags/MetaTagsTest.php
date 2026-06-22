<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Laravel\Head\Facades\Head;

it('renders generic meta tags', function (): void {
    Head::meta('theme-color', '#000000')
        ->meta('article:author', 'Taylor Otwell')
        ->meta('weird:namespace', 'Value', property: true);

    expect(Head::toHtml())
        ->toContain('<meta name="theme-color" content="#000000">')
        ->toContain('<meta property="article:author" content="Taylor Otwell">')
        ->toContain('<meta property="weird:namespace" content="Value">');
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
