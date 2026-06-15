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
        ->toContain('<title>Dashboard - Acme</title>')
        ->toContain('<meta name="description" content="Your application overview.">');
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
