<?php

declare(strict_types=1);

use Laravel\Head\Facades\Head;
use Laravel\Head\TwitterCard;

it('renders twitter image metadata', function (): void {
    Head::twitter(card: TwitterCard::SummaryLargeImage, image: 'https://example.com/twitter.jpg')
        ->twitterImage('https://example.com/twitter-alt.jpg', alt: 'Twitter alt');

    expect(Head::render())
        ->toContain('<meta name="twitter:card" content="summary_large_image">')
        ->toContain('<meta name="twitter:image" content="https://example.com/twitter-alt.jpg">')
        ->toContain('<meta name="twitter:image:alt" content="Twitter alt">');
});

it('falls back from document metadata to social metadata', function (): void {
    Head::title('Gallery')
        ->description('Gallery description.')
        ->ogImage('https://example.com/gallery.jpg', alt: 'Gallery image');

    expect(Head::render())
        ->toContain('<meta name="twitter:title" content="Gallery">')
        ->toContain('<meta name="twitter:description" content="Gallery description.">')
        ->toContain('<meta name="twitter:image" content="https://example.com/gallery.jpg">')
        ->toContain('<meta name="twitter:image:alt" content="Gallery image">');

    expect(Head::toArray()['twitter']['image'])->toBe([
        'url' => 'https://example.com/gallery.jpg',
        'alt' => 'Gallery image',
    ]);
});

it('prefers explicit twitter image metadata over open graph image metadata', function (): void {
    Head::ogImage('https://example.com/gallery.jpg', alt: 'Gallery image')
        ->twitterImage('https://example.com/twitter.jpg', alt: 'Twitter image');

    expect(Head::render())
        ->toContain('<meta name="twitter:image" content="https://example.com/twitter.jpg">')
        ->toContain('<meta name="twitter:image:alt" content="Twitter image">')
        ->not->toContain('<meta name="twitter:image" content="https://example.com/gallery.jpg">');

    expect(Head::toArray()['twitter']['image'])->toBe([
        'url' => 'https://example.com/twitter.jpg',
        'alt' => 'Twitter image',
    ]);
});
