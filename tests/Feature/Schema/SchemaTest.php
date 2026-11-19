<?php

declare(strict_types=1);

use Laravel\Head\Enums\OfferAvailability;
use Laravel\Head\Facades\Head;
use Laravel\Head\Facades\Schema;
use Laravel\Head\Tests\Fixtures\JobPosting;

it('renders built in schema objects as JSON LD', function (): void {
    Head::schema(
        Schema::article()
            ->headline('Introducing Laravel Head')
            ->description('A fluent API for the document head.')
            ->author(Schema::person()->name('Taylor Otwell'))
            ->publishedAt('2026-05-13')
            ->modifiedAt('2026-05-14')
    );

    expect(Head::toHtml())
        ->toContain('<script type="application/ld+json">')
        ->toContain('"@context":"https://schema.org"')
        ->toContain('"@type":"Article"')
        ->toContain('"description":"A fluent API for the document head."')
        ->toContain('"@type":"Person"')
        ->toContain('"datePublished":"2026-05-13"')
        ->toContain('"dateModified":"2026-05-14"');
});

it('escapes html sensitive characters in JSON LD to prevent script breakout', function (): void {
    Head::schema(
        Schema::article()->headline('</script><script>alert(1)</script>')
    );

    expect(Head::toHtml())
        ->not->toContain('</script><script>alert(1)</script>')
        ->toContain('\\u003C/script\\u003E\\u003Cscript\\u003Ealert(1)\\u003C/script\\u003E');
});

it('resolves factory methods for classes whose schema type differs from the class name', function (): void {
    Head::schema(
        Schema::breadcrumbs()
            ->item('Home', 'https://example.com')
            ->item('Shop', 'https://example.com/shop')
    )->schema(
        Schema::faq()->question('What is Laravel Head?', 'A fluent API for managing the document head.')
    );

    expect(Head::toHtml())
        ->toContain('"@type":"BreadcrumbList"')
        ->toContain('"itemListElement"')
        ->toContain('"item":"https://example.com/shop"')
        ->toContain('"@type":"FAQPage"')
        ->toContain('"@type":"Question"')
        ->toContain('"@type":"Answer"');
});

it('sets breadcrumb items in bulk with sequential positions', function (): void {
    Head::schema(
        Schema::breadcrumbs()
            ->item('Home', 'https://example.com')
            ->items([
                'Shop' => 'https://example.com/shop',
                'Shoes' => 'https://example.com/shop/shoes',
            ])
    );

    expect(Head::toHtml())
        ->toContain('"@type":"BreadcrumbList"')
        ->toContain('"position":1,"name":"Home"')
        ->toContain('"position":2,"name":"Shop"')
        ->toContain('"position":3,"name":"Shoes"')
        ->toContain('"item":"https://example.com/shop/shoes"');
});

it('sets faq questions in bulk', function (): void {
    Head::schema(
        Schema::faq()->questions([
            'What is Laravel Head?' => 'A fluent API for managing the document head.',
            'Is it free?' => 'Yes, it is open source.',
        ])
    );

    expect(Head::toHtml())
        ->toContain('"@type":"FAQPage"')
        ->toContain('"name":"What is Laravel Head?"')
        ->toContain('"name":"Is it free?"')
        ->toContain('"text":"Yes, it is open source."');
});

it('sets person image, same as and identifier properties', function (): void {
    Head::schema(
        Schema::person()
            ->name('Taylor Otwell')
            ->url('https://example.com/taylor')
            ->image('https://example.com/taylor.jpg')
            ->sameAs('https://twitter.com/taylorotwell', 'https://github.com/taylorotwell')
            ->identifier('taylorotwell')
    );

    expect(Head::toHtml())
        ->toContain('"@type":"Person"')
        ->toContain('"image":"https://example.com/taylor.jpg"')
        ->toContain('"sameAs":["https://twitter.com/taylorotwell","https://github.com/taylorotwell"]')
        ->toContain('"identifier":"taylorotwell"');
});

it('keeps a single same as url a string and accepts a property value identifier', function (): void {
    $person = Schema::person()
        ->sameAs('https://steamcommunity.com/id/example')
        ->identifier(
            Schema::make('PropertyValue')
                ->propertyID('steamId')
                ->value('76561198000000000')
        );

    expect($person->toArray())
        ->toBe([
            '@type' => 'Person',
            'sameAs' => 'https://steamcommunity.com/id/example',
            'identifier' => [
                '@type' => 'PropertyValue',
                'propertyID' => 'steamId',
                'value' => '76561198000000000',
            ],
        ]);
});

it('accepts an image object logo so the organization logo may carry its dimensions', function (): void {
    $organization = Schema::organization()
        ->name('Laravel')
        ->sameAs('https://twitter.com/laravelphp')
        ->logo(
            Schema::make('ImageObject')
                ->url('https://example.com/logo.png')
                ->width(600)
                ->height(60)
        );

    expect($organization->toArray())
        ->toBe([
            '@type' => 'Organization',
            'name' => 'Laravel',
            'sameAs' => 'https://twitter.com/laravelphp',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => 'https://example.com/logo.png',
                'width' => 600,
                'height' => 60,
            ],
        ]);
});

it('registers custom schema types as first class factory methods', function (): void {
    Schema::register(JobPosting::class);

    Head::schema(
        Schema::jobPosting()
            ->title('Senior Laravel Developer')
            ->datePosted('2026-05-13')
    );

    expect(Head::toHtml())
        ->toContain('"@type":"JobPosting"')
        ->toContain('"title":"Senior Laravel Developer"')
        ->toContain('"datePosted":"2026-05-13"');
});

it('sets offer availability from schema org enum values', function (): void {
    Head::schema(
        Schema::product()
            ->name('Desk')
            ->brand(Schema::brand()->name('Acme'))
            ->offers(
                Schema::offer()
                    ->price(125)
                    ->currency('USD')
                    ->availability(OfferAvailability::InStock)
            )
    );

    expect(Head::toHtml())
        ->toContain('"brand":{"@type":"Brand","name":"Acme"}')
        ->toContain('"priceCurrency":"USD"')
        ->toContain('"availability":"https://schema.org/InStock"');
});

it('includes all schema org item availability values', function (): void {
    expect(OfferAvailability::MadeToOrder->url())->toBe('https://schema.org/MadeToOrder')
        ->and(OfferAvailability::Reserved->url())->toBe('https://schema.org/Reserved');
});

it('throws when a magic schema property setter receives multiple arguments', function (): void {
    Schema::make('videoObject')->name('Introducing Laravel Head', 'https://example.com/video');
})->throws(
    BadMethodCallException::class,
    'Magic schema property setters accept a single value, and [Laravel\Head\Schema\GenericSchemaObject] does not define a [name] method.',
);

it('makes schema objects directly from class names without registration', function (): void {
    Head::schema(
        Schema::make(JobPosting::class)
            ->title('Senior Laravel Developer')
            ->datePosted('2026-05-13')
    );

    expect(Head::toHtml())
        ->toContain('"@type":"JobPosting"')
        ->toContain('"title":"Senior Laravel Developer"');
});
