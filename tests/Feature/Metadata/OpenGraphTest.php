<?php

declare(strict_types=1);

use Laravel\Head\Facades\Head;
use Laravel\Head\OgType;

it('renders open graph media metadata', function (): void {
    Head::og(type: OgType::Article, image: 'https://example.com/hero.jpg')
        ->ogImage(
            'https://example.com/gallery.jpg',
            alt: 'Gallery image',
            width: 1200,
            height: 630,
            type: 'image/jpeg',
            secureUrl: 'https://secure.example.com/gallery.jpg',
        );

    expect(Head::render())
        ->toContain('<meta property="og:type" content="article">')
        ->toContain('<meta property="og:image" content="https://example.com/hero.jpg">')
        ->toContain('<meta property="og:image" content="https://example.com/gallery.jpg">')
        ->toContain('<meta property="og:image:secure_url" content="https://secure.example.com/gallery.jpg">')
        ->toContain('<meta property="og:image:type" content="image/jpeg">')
        ->toContain('<meta property="og:image:width" content="1200">')
        ->toContain('<meta property="og:image:height" content="630">')
        ->toContain('<meta property="og:image:alt" content="Gallery image">');
});
