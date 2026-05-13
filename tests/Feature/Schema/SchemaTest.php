<?php

declare(strict_types=1);

use Laravel\Head\Facades\Head;
use Laravel\Head\Facades\Schema;
use Laravel\Head\Tests\Fixtures\JobPosting;

it('renders built in schema objects as JSON LD', function (): void {
    Head::schema(
        Schema::article()
            ->headline('Introducing Laravel Head')
            ->author(Schema::person()->name('Taylor Otwell'))
            ->datePublished('2026-05-13')
    );

    expect(Head::render())
        ->toContain('<script type="application/ld+json">')
        ->toContain('"@context":"https://schema.org"')
        ->toContain('"@type":"Article"')
        ->toContain('"@type":"Person"');
});

it('registers custom schema types as first class factory methods', function (): void {
    Schema::register(JobPosting::class);

    Head::schema(
        Schema::jobPosting()
            ->title('Senior Laravel Developer')
            ->datePosted('2026-05-13')
    );

    expect(Head::render())
        ->toContain('"@type":"JobPosting"')
        ->toContain('"title":"Senior Laravel Developer"')
        ->toContain('"datePosted":"2026-05-13"');
});
