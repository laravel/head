<?php

declare(strict_types=1);

use Laravel\Head\Facades\Head;

it('renders generic meta tags', function (): void {
    Head::meta('theme-color', '#000000')
        ->meta('article:author', 'Taylor Otwell')
        ->meta('weird:namespace', 'Value', property: true);

    expect(Head::toHtml())
        ->toContain('<meta name="theme-color" content="#000000">')
        ->toContain('<meta property="article:author" content="Taylor Otwell">')
        ->toContain('<meta property="weird:namespace" content="Value">');
});
