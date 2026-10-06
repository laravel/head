<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Illuminate\Support\HtmlString;
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadBuilder;

it('renders the head as an html string', function (): void {
    expect(Head::render())->toBeInstanceOf(HtmlString::class);
});

it('runs default callbacks when the head is rendered', function (): void {
    Head::defaults(fn (HeadBuilder $head) => $head->title(Route::currentRouteName() ?? 'No route'));

    Route::get('/about', fn (): string => Head::toHtml())->name('about');

    $this->get('/about')
        ->assertOk()
        ->assertSee('<title>about</title>', false);
});
