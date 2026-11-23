<?php

declare(strict_types=1);

use Laravel\Head\Enums\ItemListOrder;
use Laravel\Head\Facades\Head;
use Laravel\Head\Facades\Schema;
use Laravel\Head\Schema\ItemList;

it('registers item list as a first class factory method', function (): void {
    expect(Schema::itemList())->toBeInstanceOf(ItemList::class);
});

it('renders a list of named items with sequential positions', function (): void {
    Head::schema(
        Schema::itemList()
            ->name('Most played killers')
            ->url('https://example.com/leaderboards')
            ->item('The Trapper', 'https://example.com/killers/trapper')
            ->item('The Nurse', 'https://example.com/killers/nurse')
    );

    expect(Head::toHtml())
        ->toContain('"@type":"ItemList"')
        ->toContain('"name":"Most played killers"')
        ->toContain('"position":1,"name":"The Trapper","url":"https://example.com/killers/trapper"')
        ->toContain('"position":2,"name":"The Nurse","url":"https://example.com/killers/nurse"');
});

it('wraps schema objects in list items', function (): void {
    expect(Schema::itemList()->item(Schema::person()->name('Taylor Otwell'))->toArray())
        ->toBe([
            '@type' => 'ItemList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'item' => ['@type' => 'Person', 'name' => 'Taylor Otwell'],
                ],
            ],
        ]);
});

it('leaves out the url of an item that has none', function (): void {
    expect(Schema::itemList()->item('The Trapper')->toArray()['itemListElement'])
        ->toBe([['@type' => 'ListItem', 'position' => 1, 'name' => 'The Trapper']]);
});

it('sets item list items in bulk', function (): void {
    $list = Schema::itemList()
        ->item('Home', 'https://example.com')
        ->items([
            'Shop' => 'https://example.com/shop',
            'Shoes' => 'https://example.com/shop/shoes',
        ])
        ->items([Schema::person()->name('Taylor Otwell')]);

    expect($list->toArray()['itemListElement'])
        ->toBe([
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'url' => 'https://example.com'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Shop', 'url' => 'https://example.com/shop'],
            ['@type' => 'ListItem', 'position' => 3, 'name' => 'Shoes', 'url' => 'https://example.com/shop/shoes'],
            ['@type' => 'ListItem', 'position' => 4, 'item' => ['@type' => 'Person', 'name' => 'Taylor Otwell']],
        ]);
});

it('sets item list order from schema org enum values', function (): void {
    expect(Schema::itemList()->itemListOrder(ItemListOrder::Descending)->toArray())
        ->toBe(['@type' => 'ItemList', 'itemListOrder' => 'https://schema.org/ItemListOrderDescending']);
});

it('includes all schema org item list order values', function (): void {
    expect(ItemListOrder::Ascending->url())->toBe('https://schema.org/ItemListOrderAscending')
        ->and(ItemListOrder::Unordered->url())->toBe('https://schema.org/ItemListUnordered');
});
