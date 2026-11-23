<?php

declare(strict_types=1);

use Laravel\Head\Facades\Head;
use Laravel\Head\Facades\Schema;
use Laravel\Head\Schema\ProfilePage;

it('registers profile page as a first class factory method', function (): void {
    expect(Schema::profilePage())->toBeInstanceOf(ProfilePage::class);
});

it('renders a profile page around the person it describes', function (): void {
    Head::schema(
        Schema::profilePage()
            ->url('https://example.com/taylor')
            ->dateCreated('2011-06-09')
            ->dateModified(new DateTimeImmutable('2026-05-14T10:00:00+00:00'))
            ->mainEntity(
                Schema::person()
                    ->name('Taylor Otwell')
                    ->url('https://example.com/taylor')
            )
    );

    expect(Head::toHtml())
        ->toContain('"@type":"ProfilePage"')
        ->toContain('"url":"https://example.com/taylor"')
        ->toContain('"dateCreated":"2011-06-09"')
        ->toContain('"dateModified":"2026-05-14T10:00:00+00:00"')
        ->toContain('"mainEntity":{"@type":"Person","name":"Taylor Otwell"');
});

it('accepts an organization as the profile main entity', function (): void {
    expect(Schema::profilePage()->mainEntity(Schema::organization()->name('Laravel'))->toArray())
        ->toBe([
            '@type' => 'ProfilePage',
            'mainEntity' => ['@type' => 'Organization', 'name' => 'Laravel'],
        ]);
});

it('inherits the web page name and description setters', function (): void {
    expect(Schema::profilePage()->name('Taylor Otwell')->description('Creator of Laravel.')->toArray())
        ->toBe([
            '@type' => 'ProfilePage',
            'name' => 'Taylor Otwell',
            'description' => 'Creator of Laravel.',
        ]);
});
