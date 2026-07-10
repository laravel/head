<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Support\Header;
use Laravel\Head\Enums\Media;
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadManager;

it('renders theme color tags', function (): void {
    Head::themeColor('#0f172a');

    expect(Head::toHtml())->toContain('<meta name="theme-color" content="#0f172a">');
});

it('renders media-specific theme colors from media enum values', function (): void {
    Head::themeColor('#ffffff', media: Media::Light)
        ->themeColor('#111827', media: Media::Dark);

    expect(Head::toHtml())
        ->toContain('<meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">')
        ->toContain('<meta name="theme-color" content="#111827" media="(prefers-color-scheme: dark)">');
});

it('provides common media query values', function (): void {
    expect(Media::Light->value)->toBe('(prefers-color-scheme: light)')
        ->and(Media::Dark->value)->toBe('(prefers-color-scheme: dark)')
        ->and(Media::Portrait->value)->toBe('(orientation: portrait)')
        ->and(Media::Landscape->value)->toBe('(orientation: landscape)');
});

it('resolves theme colors from route attributes', function (): void {
    Route::get('/product', fn (): string => Head::toHtml())->metadata(Head::forMetadata(
        themeColor: '#0f172a',
    ));

    $this->get('/product')
        ->assertOk()
        ->assertSee('<meta name="theme-color" content="#0f172a">', false);
});

it('shares stable inertia keys for theme colors', function (): void {
    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->metadata(Head::forMetadata(
        themeColor: '#0f172a',
    ));

    $this->get('/dashboard', [Header::INERTIA => 'true'])
        ->assertOk()
        ->assertJsonPath('props.'.HeadManager::INERTIA_PROP.'.0', '<meta data-inertia="themeColor" name="theme-color" content="#0f172a">');
});
