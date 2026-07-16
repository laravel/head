<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('renders fresh route head data in livewire navigate documents', function (): void {
    Route::get('/first', fn () => view('livewire-layout', ['href' => '/second']))->withHead(
        title: 'First Page',
        description: 'The first page.',
    );

    Route::get('/second', fn () => view('livewire-layout', ['href' => '/first']))->withHead(
        title: 'Second Page',
        description: 'The second page.',
    );

    $this->get('/first')
        ->assertOk()
        ->assertSee('<title>First Page</title>', false)
        ->assertSee('<meta name="description" content="The first page.">', false)
        ->assertSee('wire:navigate', false)
        ->assertSee('data-navigate-once', false);

    $this->get('/second')
        ->assertOk()
        ->assertSee('<title>Second Page</title>', false)
        ->assertSee('<meta name="description" content="The second page.">', false);
});
