<?php

declare(strict_types=1);

use Laravel\Head\Rendering\TagRenderer;

it('drops attributes whose name contains whitespace or an equals sign', function (): void {
    $tags = new TagRenderer;

    expect($tags->attributes(['onerror x' => '1']))->toBe('')
        ->and($tags->attributes(['foo=bar' => '1']))->toBe('')
        ->and($tags->attributes(['foo"bar' => '1']))->toBe('')
        ->and($tags->attributes(["foo\nbar" => '1']))->toBe('')
        ->and($tags->attributes(['onerror x' => true]))->toBe('');
});

it('keeps well-formed attribute names', function (): void {
    $tags = new TagRenderer;

    expect($tags->attributes(['data-inertia' => 'title']))->toBe('data-inertia="title"')
        ->and($tags->attributes(['crossorigin' => true]))->toBe('crossorigin')
        ->and($tags->attributes(['aria-label' => 'Close', 'tabindex' => '0']))->toBe('aria-label="Close" tabindex="0"');
});

it('does not let a malicious attribute name break out of a link tag', function (): void {
    $tags = new TagRenderer;

    $html = $tags->linkWithAttributes('stylesheet', [
        'href' => '/theme.css',
        'onerror x' => '1',
    ]);

    expect($html)
        ->toBe('<link rel="stylesheet" href="/theme.css">')
        ->not->toContain('onerror');
});
