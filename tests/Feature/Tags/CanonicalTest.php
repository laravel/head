<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadManager;

it('does not render a canonical URL by default', function (): void {
    expect(Head::toHtml())->not->toContain('rel="canonical"');
});

it('carries inherited canonical options into later canonical URLs', function (): void {
    Head::defaults(fn (HeadManager $head): HeadManager => $head->canonical(forceHttps: false, trailingSlash: true));

    Route::get('/about', fn (): string => Head::toHtml())
        ->metadata(Head::route(canonical: '/about'));

    $this->get('/about')
        ->assertOk()
        ->assertSee('<link rel="canonical" href="http://localhost/about/">', false);
});
