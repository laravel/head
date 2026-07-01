<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Support\Header;
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadManager;

it('renders theme color tags', function (): void {
    Head::themeColor('#0f172a');

    expect(Head::toHtml())->toContain('<meta name="theme-color" content="#0f172a">');
});

it('resolves theme colors from route attributes', function (): void {
    Route::get('/product', fn (): string => Head::toHtml())->metadata(Head::route(
        themeColor: '#0f172a',
    ));

    $this->get('/product')
        ->assertOk()
        ->assertSee('<meta name="theme-color" content="#0f172a">', false);
});

it('shares stable inertia keys for theme colors', function (): void {
    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->metadata(Head::route(
        themeColor: '#0f172a',
    ));

    $this->get('/dashboard', [Header::INERTIA => 'true'])
        ->assertOk()
        ->assertJsonPath('props.'.HeadManager::INERTIA_PROP.'.0', '<meta data-inertia="themeColor" name="theme-color" content="#0f172a">');
});
