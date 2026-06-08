<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Laravel\Head\Facades\Head;

it('does not render a canonical URL by default', function (): void {
    expect(Head::render())->not->toContain('rel="canonical"');
});

it('can suppress an inherited canonical URL', function (): void {
    Head::defaults(fn (Laravel\Head\Head $head): Laravel\Head\Head => $head->canonical());

    Head::canonical(false);

    expect(Head::render())->not->toContain('rel="canonical"');
});

it('carries inherited canonical options into later canonical URLs', function (): void {
    Head::defaults(fn (Laravel\Head\Head $head): Laravel\Head\Head => $head->canonical(forceHttps: false, trailingSlash: true));

    Route::get('/about', fn (): string => Head::render())
        ->withHead(canonical: '/about');

    $this->get('/about')
        ->assertOk()
        ->assertSee('<link rel="canonical" href="http://localhost/about/">', false);
});
