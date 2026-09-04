<?php

declare(strict_types=1);

use Laravel\Head\Enums\EventAttendanceMode;
use Laravel\Head\Enums\EventStatus;
use Laravel\Head\Enums\OfferAvailability;
use Laravel\Head\Facades\Head;
use Laravel\Head\Facades\Schema;
use Laravel\Head\Schema\Place;

it('renders an event with the properties required for rich results', function (): void {
    Head::schema(
        Schema::event()
            ->name('Laracon US')
            ->startDate('2026-08-20T09:00:00-05:00')
            ->endDate(new DateTimeImmutable('2026-08-21T17:00:00-05:00'))
            ->location(
                Schema::place()
                    ->name('Music City Center')
                    ->address('201 Rep. John Lewis Way S, Nashville, TN')
            )
    );

    expect(Head::toHtml())
        ->toContain('"@type":"Event"')
        ->toContain('"name":"Laracon US"')
        ->toContain('"startDate":"2026-08-20T09:00:00-05:00"')
        ->toContain('"endDate":"2026-08-21T17:00:00-05:00"')
        ->toContain('"location":{"@type":"Place","name":"Music City Center","address":"201 Rep. John Lewis Way S, Nashville, TN"}');
});

it('treats a string location as the name of a place', function (): void {
    $event = Schema::event()->location('Music City Center');

    expect($event->toArray()['location'])->toBe(['@type' => 'Place', 'name' => 'Music City Center']);
});

it('sets event status and attendance mode from schema org enum values', function (): void {
    $event = Schema::event()
        ->eventStatus(EventStatus::Scheduled)
        ->eventAttendanceMode(EventAttendanceMode::Offline);

    expect($event->toArray())
        ->toMatchArray([
            'eventStatus' => 'https://schema.org/EventScheduled',
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
        ]);
});

it('sets event images, performers, organizer and offers', function (): void {
    $event = Schema::event()
        ->image(['https://example.com/hero.jpg', 'https://example.com/stage.jpg'])
        ->performer([Schema::person()->name('Taylor Otwell'), Schema::organization()->name('Laravel')])
        ->organizer(Schema::organization()->name('Laravel'))
        ->offers(
            Schema::offer()
                ->price(499)
                ->currency('USD')
                ->availability(OfferAvailability::InStock)
        );

    expect($event->toArray())
        ->toMatchArray([
            'image' => ['https://example.com/hero.jpg', 'https://example.com/stage.jpg'],
            'performer' => [
                ['@type' => 'Person', 'name' => 'Taylor Otwell'],
                ['@type' => 'Organization', 'name' => 'Laravel'],
            ],
            'organizer' => ['@type' => 'Organization', 'name' => 'Laravel'],
        ])
        ->and($event->toArray()['offers'])->toMatchArray(['@type' => 'Offer', 'price' => 499, 'priceCurrency' => 'USD']);
});

it('registers place as a first class factory method', function (): void {
    expect(Schema::place())->toBeInstanceOf(Place::class)
        ->and(Schema::place()->name('Venue')->url('https://example.com/venue')->toArray())
        ->toBe(['@type' => 'Place', 'name' => 'Venue', 'url' => 'https://example.com/venue']);
});

it('includes all schema org event status and attendance mode values', function (): void {
    expect(EventStatus::Cancelled->url())->toBe('https://schema.org/EventCancelled')
        ->and(EventStatus::MovedOnline->url())->toBe('https://schema.org/EventMovedOnline')
        ->and(EventAttendanceMode::Mixed->url())->toBe('https://schema.org/MixedEventAttendanceMode');
});
