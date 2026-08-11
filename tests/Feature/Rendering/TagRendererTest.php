<?php

declare(strict_types=1);

use Laravel\Head\Rendering\TagRenderer;

it('renders valid HTML attribute names without changing them', function (): void {
    $tags = new TagRenderer;

    expect($tags->attributes(['data-inertia' => 'title']))->toBe('data-inertia="title"')
        ->and($tags->attributes(['crossorigin' => true]))->toBe('crossorigin')
        ->and($tags->attributes(['aria-label' => 'Close', 'tabindex' => '0']))->toBe('aria-label="Close" tabindex="0"')
        ->and($tags->attributes(['@click' => 'close', '-custom' => 'value']))->toBe('@click="close" -custom="value"')
        ->and($tags->attributes(['1custom' => 'value', 'café' => 'yes']))->toBe('1custom="value" café="yes"')
        ->and($tags->attributes(['foo&bar' => 'value']))->toBe('foo&bar="value"')
        ->and($tags->attributes(['123' => 'value']))->toBe('123="value"');
});

it('rejects invalid HTML attribute names', function (string $name): void {
    $tags = new TagRenderer;

    expect(fn (): string => $tags->attributes([$name => 'value']))
        ->toThrow(InvalidArgumentException::class, 'Invalid HTML attribute name.');
})->with([
    'empty' => '',
    'space' => 'onerror x',
    'equals' => 'foo=bar',
    'double quote' => 'foo"bar',
    'single quote' => "foo'bar",
    'slash' => 'foo/bar',
    'greater than' => 'foo>bar',
    'control' => "foo\nbar",
    'invalid utf-8' => "foo\xC3\x28bar",
]);

it('validates attribute names supplied directly to tag methods', function (): void {
    $tags = new TagRenderer;

    expect(fn (): string => $tags->metaWithAttributes('name onerror', 'description', ['content' => 'Value']))
        ->toThrow(InvalidArgumentException::class, 'Invalid HTML attribute name.');
});

it('rejects case-insensitive duplicate attributes', function (): void {
    $tags = new TagRenderer;

    expect(fn (): string => $tags->linkWithAttributes('stylesheet', [
        'REL' => 'alternate',
        'href' => '/theme.css',
    ]))->toThrow(InvalidArgumentException::class, 'Duplicate HTML attribute name [REL].');
});
