<?php

declare(strict_types=1);

use Illuminate\Support\HtmlString;
use Laravel\Head\Facades\Head;

it('renders the head as an html string', function (): void {
    expect(Head::render())->toBeInstanceOf(HtmlString::class);
});
