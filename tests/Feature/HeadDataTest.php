<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadManager;
use Laravel\Head\Metadata\Title;
use Laravel\Head\Tests\Fixtures\ReadingTime;

afterEach(function (): void {
    HeadManager::flushMacros();
});

it('renders a registered custom metadata section', function (): void {
    Head::extend(ReadingTime::class);

    Head::macro('readingTime', function (int $minutes): HeadManager {
        $this->data()->set(ReadingTime::make($minutes));

        return $this;
    });

    Head::readingTime(7);

    expect(Head::toHtml())
        ->toContain('<meta name="twitter:label1" content="Reading time">')
        ->toContain('<meta name="twitter:data1" content="7 min read">');

    expect(Head::toArray()['readingTime'])->toBe(7);
});

it('resolves custom section values from route attributes', function (): void {
    Head::extend(ReadingTime::class);

    Route::get('/article', fn (): string => Head::toHtml())
        ->withHead(readingTime: 4);

    $this->get('/article')
        ->assertOk()
        ->assertSee('<meta name="twitter:data1" content="4 min read">', false);
});

it('rejects metadata extensions that do not extend metadata', function (): void {
    expect(fn (): HeadManager => Head::extend(stdClass::class))
        ->toThrow(InvalidArgumentException::class, 'Head metadata extensions must extend');
});

it('rejects built in metadata extensions', function (): void {
    expect(fn (): HeadManager => Head::extend(Title::class))
        ->toThrow(InvalidArgumentException::class, 'Built-in head metadata sections are already registered.');
});
