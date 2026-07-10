<?php

declare(strict_types=1);

use Laravel\Head\Facades\Head;
use Laravel\Head\HeadBuilder;
use Laravel\Head\HeadData;
use Laravel\Head\HeadManager;
use Laravel\Head\Schema\SchemaFactory;
use Laravel\Head\Tags\Schemas;
use Laravel\Head\Tags\Title;

it('builds head data without the container', function (): void {
    $data = new HeadData;

    (new HeadBuilder($data))
        ->title('About')
        ->description('About Acme.')
        ->schema(fn (SchemaFactory $schema) => $schema->make('webPage')->set('name', 'About'));

    expect($data->isEmpty())->toBeFalse();
    expect($data->get(Title::class))->not->toBeNull();
    expect($data->get(Schemas::class))->not->toBeNull();
});

it('applies conditional definitions with when and unless', function (): void {
    $data = new HeadData;

    (new HeadBuilder($data))
        ->when(true, fn (HeadBuilder $head) => $head->title('Conditional'))
        ->unless(true, fn (HeadBuilder $head) => $head->description('Skipped'));

    expect($data->get(Title::class))->not->toBeNull();
    expect($data->isEmpty())->toBeFalse();
});

it('supports conditional runtime definitions through the facade', function (): void {
    Head::when(true, fn (HeadManager $head) => $head->title('Conditional'));
    Head::unless(true, fn (HeadManager $head) => $head->description('Skipped'));

    expect(Head::toHtml())
        ->toContain('<title>Conditional</title>')
        ->not->toContain('Skipped');
});

it('receives a head builder in defaults callbacks', function (): void {
    Head::defaults(function (HeadBuilder $head): void {
        $head->title('Acme', suffix: ' - Acme');
    });

    Head::title('About');

    expect(Head::toHtml())->toContain('<title>About - Acme</title>');
});

it('chains runtime definitions into rendering', function (): void {
    $html = Head::title('About')
        ->description('About Acme.')
        ->toHtml();

    expect($html)
        ->toContain('<title>About</title>')
        ->toContain('<meta name="description" content="About Acme.">');
});
