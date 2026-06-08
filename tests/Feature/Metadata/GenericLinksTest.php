<?php

declare(strict_types=1);

use Laravel\Head\Facades\Head;

it('renders generic link tags', function (): void {
    Head::link('manifest', '/manifest.json')
        ->link('apple-touch-icon', '/apple-touch-icon.png', ['sizes' => '180x180']);

    expect(Head::render())
        ->toContain('<link rel="manifest" href="/manifest.json">')
        ->toContain('<link rel="apple-touch-icon" href="/apple-touch-icon.png" sizes="180x180">');
});
