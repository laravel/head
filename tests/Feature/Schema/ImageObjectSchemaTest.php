<?php

declare(strict_types=1);

use Laravel\Head\Facades\Head;
use Laravel\Head\Facades\Schema;
use Laravel\Head\Schema\ImageObject;

it('registers image object as a first class factory method', function (): void {
    expect(Schema::imageObject())->toBeInstanceOf(ImageObject::class);
});

it('renders an image object with its dimensions', function (): void {
    Head::schema(
        Schema::imageObject()
            ->url('https://example.com/logo.png')
            ->width(600)
            ->height(60)
    );

    expect(Head::toHtml())
        ->toContain('"@type":"ImageObject"')
        ->toContain('"url":"https://example.com/logo.png"')
        ->toContain('"width":600')
        ->toContain('"height":60');
});

it('accepts css dimensions as strings', function (): void {
    expect(Schema::imageObject()->width('600px')->height('60px')->toArray())
        ->toBe(['@type' => 'ImageObject', 'width' => '600px', 'height' => '60px']);
});

it('nests an image object inside another schema object', function (): void {
    expect(Schema::webPage()->primaryImageOfPage(Schema::imageObject()->url('https://example.com/hero.jpg'))->toArray())
        ->toBe([
            '@type' => 'WebPage',
            'primaryImageOfPage' => [
                '@type' => 'ImageObject',
                'url' => 'https://example.com/hero.jpg',
            ],
        ]);
});
