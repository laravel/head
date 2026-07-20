<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Laravel\Head\Enums\RobotsRule;
use Laravel\Head\Facades\Head;

it('does not render robots tags by default', function (): void {
    expect(Head::toHtml())->not->toContain('name="robots"');
});

it('renders robots tags from strings', function (): void {
    Head::robots('noindex, nofollow');

    expect(Head::toHtml())->toContain('<meta name="robots" content="noindex, nofollow">');
});

it('renders robots tags from rules', function (): void {
    Head::robots([RobotsRule::NoIndex, RobotsRule::NoFollow]);

    expect(Head::toHtml())->toContain('<meta name="robots" content="noindex, nofollow">');
});

it('renders robots tags from a single rule', function (): void {
    Head::robots(RobotsRule::NoIndex);

    expect(Head::toHtml())->toContain('<meta name="robots" content="noindex">');
});

it('renders tags for pages searchable by robots', function (): void {
    Head::searchableByRobots();

    expect(Head::toHtml())->toContain('<meta name="robots" content="all">');
});

it('renders tags for pages hidden from robots', function (): void {
    Head::hiddenFromRobots();

    expect(Head::toHtml())->toContain('<meta name="robots" content="none">');
});

it('renders robots tags from mixed string and enum rules', function (): void {
    Head::robots(['noindex', RobotsRule::NoFollow]);

    expect(Head::toHtml())->toContain('<meta name="robots" content="noindex, nofollow">');
});

it('resolves robots rules from route attributes', function (): void {
    Route::get('/private', fn (): string => Head::toHtml())
        ->withHead(robots: [RobotsRule::NoIndex, RobotsRule::NoFollow]);

    $this->get('/private')
        ->assertOk()
        ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
});
