<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Support\Header;
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadManager;

it('shares rendered head elements with inertia page objects', function (): void {
    Head::defaults(fn (HeadManager $head) => $head->title('Acme', suffix: ' - Acme'));

    Route::get('/dashboard', fn () => Inertia::render('Dashboard', [
        'user' => 'Taylor',
    ]))->withHead(
        title: 'Dashboard',
        description: 'Your application overview.',
    );

    $response = $this->get('/dashboard', [Header::INERTIA => 'true'])
        ->assertOk()
        ->assertJsonPath('component', 'Dashboard')
        ->assertJsonPath('props.user', 'Taylor');

    $elements = $response->json('props.head');

    expect($elements)->toBeArray()
        ->toContain('<title data-inertia="title">Dashboard - Acme</title>')
        ->toContain('<meta data-inertia="description" name="description" content="Your application overview.">');
});

it('shares stable semantic inertia keys for each head element', function (): void {
    Route::get('/lean', fn () => Inertia::render('Page'))->withHead(
        description: 'Lean page.',
    );

    Route::get('/rich', fn () => Inertia::render('Page'))->withHead(
        title: 'Rich page',
        description: 'Rich page.',
        preload: [['href' => '/fonts/inter.woff2', 'as' => 'font']],
        ogImage: 'https://example.com/rich.jpg',
    );

    $lean = $this->get('/lean', [Header::INERTIA => 'true'])->json('props.head');
    $rich = $this->get('/rich', [Header::INERTIA => 'true'])->json('props.head');

    expect($lean)
        ->toContain('<meta data-inertia="description" name="description" content="Lean page.">')
        ->each->toContain(' data-inertia="');

    expect($rich)
        ->toContain('<title data-inertia="title">Rich page</title>')
        ->toContain('<meta data-inertia="description" name="description" content="Rich page.">')
        ->each->toContain(' data-inertia="');
});

it('keeps head elements off the wire during inertia partial reloads', function (): void {
    Route::get('/dashboard', fn () => Inertia::render('Dashboard', [
        'user' => 'Taylor',
    ]))->withHead(title: 'Dashboard');

    $this->get('/dashboard', [
        Header::INERTIA => 'true',
        Header::PARTIAL_COMPONENT => 'Dashboard',
        Header::PARTIAL_ONLY => 'user',
    ])
        ->assertOk()
        ->assertJsonPath('props.user', 'Taylor')
        ->assertJsonMissingPath('props.head');
});
