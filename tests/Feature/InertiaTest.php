<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Support\Header;
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadManager;

it('injects resolved head data into inertia page objects', function (): void {
    Head::defaults(fn (HeadManager $head) => $head->title('Acme', suffix: ' - Acme'));

    Route::get('/dashboard', fn () => Inertia::render('Dashboard', [
        'user' => 'Taylor',
    ]))->withHead(
        title: 'Dashboard',
        description: 'Your application overview.',
    );

    $this->get('/dashboard', [Header::INERTIA => 'true'])
        ->assertOk()
        ->assertJsonPath('component', 'Dashboard')
        ->assertJsonPath('props.user', 'Taylor')
        ->assertJsonPath('props.head.title', 'Dashboard - Acme')
        ->assertJsonPath('props.head.description', 'Your application overview.');
});

it('keeps head data in inertia partial reloads', function (): void {
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
        ->assertJsonPath('props.head.title', 'Dashboard');
});
