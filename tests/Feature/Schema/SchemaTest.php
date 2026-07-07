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
            ->datePublished('2026-05-13')
    );

    expect(Head::toHtml())
        ->toContain('<script type="application/ld+json">')
        ->toContain('"@context":"https://schema.org"')
        ->toContain('"@type":"Article"')
        ->toContain('"description":"A fluent API for the document head."')
        ->toContain('"@type":"Person"');
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
            ->offers(
                Schema::offer()
                    ->price(125)
                    ->priceCurrency('USD')
                    ->availability(OfferAvailability::InStock)
            )
    );

    expect(Head::toHtml())
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
