<?php

declare(strict_types=1);

use Laravel\Head\Enums\OfferAvailability;
use Laravel\Head\Facades\Head;
use Laravel\Head\Facades\Schema;
use Laravel\Head\Schema\SoftwareApplication;
use Laravel\Head\Schema\WebApplication;

it('registers software application and web application as first class factory methods', function (): void {
    expect(Schema::softwareApplication())->toBeInstanceOf(SoftwareApplication::class)
        ->and(Schema::webApplication())->toBeInstanceOf(WebApplication::class);
});

it('renders a software application with the properties required for rich results', function (): void {
    Head::schema(
        Schema::softwareApplication()
            ->name('Laravel Nightwatch')
            ->description('Monitoring for Laravel applications.')
            ->url('https://example.com/nightwatch')
            ->applicationCategory('DeveloperApplication')
            ->operatingSystem('Any')
            ->offers(
                Schema::offer()
                    ->price(0)
                    ->currency('USD')
                    ->availability(OfferAvailability::InStock)
            )
    );

    expect(Head::toHtml())
        ->toContain('"@type":"SoftwareApplication"')
        ->toContain('"name":"Laravel Nightwatch"')
        ->toContain('"applicationCategory":"DeveloperApplication"')
        ->toContain('"operatingSystem":"Any"')
        ->toContain('"offers":{"@type":"Offer","price":0,"priceCurrency":"USD","availability":"https://schema.org/InStock"}');
});

it('renders a web application as its own schema type', function (): void {
    expect(Schema::webApplication()->name('Dead by Stats')->operatingSystem('Any')->toArray())
        ->toBe([
            '@type' => 'WebApplication',
            'name' => 'Dead by Stats',
            'operatingSystem' => 'Any',
        ]);
});

it('accepts several offers and images on an application', function (): void {
    $application = Schema::softwareApplication()
        ->image(['https://example.com/icon.png', 'https://example.com/screenshot.png'])
        ->offers([Schema::offer()->price(0)->currency('USD'), Schema::offer()->price(9)->currency('USD')]);

    expect($application->toArray())
        ->toMatchArray([
            'image' => ['https://example.com/icon.png', 'https://example.com/screenshot.png'],
            'offers' => [
                ['@type' => 'Offer', 'price' => 0, 'priceCurrency' => 'USD'],
                ['@type' => 'Offer', 'price' => 9, 'priceCurrency' => 'USD'],
            ],
        ]);
});
