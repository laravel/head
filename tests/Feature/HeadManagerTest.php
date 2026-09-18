<?php

declare(strict_types=1);

use Illuminate\Container\Container;
use Illuminate\Support\HtmlString;
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadManager;

it('renders the head as an html string', function (): void {
    expect(Head::render())->toBeInstanceOf(HtmlString::class);
});

it('keeps request head data in the container handling the request', function (): void {
    $worker = app();
    $manager = $worker->make(HeadManager::class);

    $first = clone $worker;
    Container::setInstance($first);

    try {
        $manager->title('First request');
    } finally {
        Container::setInstance($worker);
    }

    $second = clone $worker;
    Container::setInstance($second);

    try {
        expect($manager->toHtml())->not->toContain('First request');
    } finally {
        Container::setInstance($worker);
    }
});
