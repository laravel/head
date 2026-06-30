<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadManager;
use Laravel\Head\Tags\Title;
use Laravel\Head\Tests\Fixtures\ConflictingRouteAttribute;
use Laravel\Head\Tests\Fixtures\ReadingTime;

afterEach(function (): void {
    HeadManager::flushMacros();
});

it('renders a registered custom tag builder', function (): void {
    Head::extend(ReadingTime::class);

    Head::macro('readingTime', function (int $minutes): HeadManager {
        $this->data()->overlayBuilder(ReadingTime::make($minutes));

        return $this;
    });

    Head::readingTime(7);

    expect(Head::toHtml())
        ->toContain('<meta name="twitter:label1" content="Reading time">')
        ->toContain('<meta name="twitter:data1" content="7 min read">');

    expect(Head::toArray()['readingTime'])->toBe(7);
});

it('resolves custom builder values from route attributes', function (): void {
    Head::extend(ReadingTime::class);

    Route::get('/article', fn (): string => Head::toHtml())
        ->withHead(readingTime: 4);

    $this->get('/article')
        ->assertOk()
        ->assertSee('<meta name="twitter:data1" content="4 min read">', false);
});

it('rejects tag extensions that do not extend tag builders', function (): void {
    expect(fn (): HeadManager => Head::extend(stdClass::class))
        ->toThrow(InvalidArgumentException::class, 'Head tag extensions must extend');
});

it('rejects built in tag builders', function (): void {
    expect(fn (): HeadManager => Head::extend(Title::class))
        ->toThrow(InvalidArgumentException::class, 'Built-in head tag builders are already registered.');
});

it('rejects tag extensions that register duplicate route attribute keys', function (): void {
    expect(fn (): HeadManager => Head::extend(ConflictingRouteAttribute::class))
        ->toThrow(InvalidArgumentException::class, 'Head tag extensions may not register route attributes already registered by another builder: title.');
});
