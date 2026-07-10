<?php

declare(strict_types=1);

use Laravel\Head\Enums\ImageType;
use Laravel\Head\Enums\OgType;
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadBuilder;

it('renders open graph media tags', function (): void {
    Head::og(type: OgType::Article, image: 'https://example.com/hero.jpg')
        ->ogImage(
            'https://example.com/gallery.jpg',
            alt: 'Gallery image',
            width: 1200,
            height: 630,
            type: ImageType::Jpeg,
            secureUrl: 'https://secure.example.com/gallery.jpg',
        );

    expect(Head::toHtml())
        ->toContain('<meta property="og:type" content="article">')
        ->toContain('property="og:image" content="https://example.com/hero.jpg">')
        ->toContain('property="og:image" content="https://example.com/gallery.jpg">')
        ->toContain('property="og:image:secure_url" content="https://secure.example.com/gallery.jpg">')
        ->toContain('property="og:image:type" content="image/jpeg">')
        ->toContain('property="og:image:width" content="1200">')
        ->toContain('property="og:image:height" content="630">')
        ->toContain('property="og:image:alt" content="Gallery image">');
});

it('replaces default layer og images with page level og images', function (): void {
    Head::defaults(fn (HeadBuilder $head) => $head->ogImage('https://example.com/brand.png', alt: 'Brand card'));

    Head::ogImage('https://example.com/product.jpg', alt: 'Product photo');

    expect(Head::toHtml())
        ->toContain('property="og:image" content="https://example.com/product.jpg">')
        ->not->toContain('https://example.com/brand.png');
});

it('falls back to default layer og images when no higher layer defines any', function (): void {
    Head::defaults(fn (HeadBuilder $head) => $head->ogImage('https://example.com/brand.png'));

    Head::title('About');

    expect(Head::toHtml())
        ->toContain('property="og:image" content="https://example.com/brand.png">');
});

it('keeps default layer og videos when a higher layer only defines images', function (): void {
    Head::defaults(fn (HeadBuilder $head) => $head
        ->ogImage('https://example.com/brand.png')
        ->ogVideo('https://example.com/tour.mp4'));

    Head::ogImage('https://example.com/product.jpg');

    expect(Head::toHtml())
        ->toContain('property="og:image" content="https://example.com/product.jpg">')
        ->toContain('property="og:video" content="https://example.com/tour.mp4">')
        ->not->toContain('https://example.com/brand.png');
});

it('merges og images between page level layers', function (): void {
    Head::ogImage('https://example.com/first.jpg')
        ->ogImage('https://example.com/second.jpg');

    expect(Head::toHtml())
        ->toContain('property="og:image" content="https://example.com/first.jpg">')
        ->toContain('property="og:image" content="https://example.com/second.jpg">');
});
