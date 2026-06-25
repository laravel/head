<?php

declare(strict_types=1);

use Laravel\Head\Facades\Head;
use Laravel\Head\HeadManager;
use Laravel\Head\TagRegistry;

it('registers the head manager', function (): void {
    expect(app('head'))->toBeInstanceOf(HeadManager::class);
});

it('registers the tag registry', function (): void {
    expect(app(TagRegistry::class))->toBeInstanceOf(TagRegistry::class);
});

it('registers the head facade', function (): void {
    expect(Head::getFacadeRoot())->toBeInstanceOf(HeadManager::class);
});

it('registers the blade directive', function (): void {
    $compiled = app('blade.compiler')->compileString('@head');

    expect($compiled)->toContain("app('head')->renderForView(get_defined_vars())");
});
