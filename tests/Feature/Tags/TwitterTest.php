<?php

declare(strict_types=1);

use Laravel\Head\Enums\TwitterCard;
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadBuilder;

it('renders twitter image tags', function (): void {
    Head::twitter(card: TwitterCard::SummaryLargeImage, image: 'https://example.com/twitter.jpg')
        ->twitterImage('https://example.com/twitter-alt.jpg', alt: 'Twitter alt');

    expect(Head::toHtml())
        ->toContain('<meta name="twitter:card" content="summary_large_image">')
        ->toContain('<meta name="twitter:image" content="https://example.com/twitter-alt.jpg">')
        ->toContain('<meta name="twitter:image:alt" content="Twitter alt">');
});

it('does not render twitter fallback tags by default', function (): void {
    Head::title('Gallery')
        ->description('Gallery description.')
        ->ogImage('https://example.com/gallery.jpg', alt: 'Gallery image');

    expect(Head::toHtml())
        ->not->toContain('name="twitter:title"')
        ->not->toContain('name="twitter:description"')
        ->not->toContain('name="twitter:image"');

    expect(Head::toArray()['twitter'])->toBe([]);
});

it('falls back from document tags to twitter tags when configured', function (): void {
    Head::defaults(fn (HeadBuilder $head): HeadBuilder => $head->twitter());

    Head::title('Gallery')
        ->description('Gallery description.')
        ->ogImage('https://example.com/gallery.jpg', alt: 'Gallery image');

    expect(Head::toHtml())
        ->toContain('<meta name="twitter:title" content="Gallery">')
        ->toContain('<meta name="twitter:description" content="Gallery description.">')
        ->toContain('<meta name="twitter:image" content="https://example.com/gallery.jpg">')
        ->toContain('<meta name="twitter:image:alt" content="Gallery image">');

    expect(Head::toArray()['twitter']['image'])->toBe([
        'url' => 'https://example.com/gallery.jpg',
        'alt' => 'Gallery image',
    ]);
});

it('prefers explicit twitter image tags over open graph image tags', function (): void {
    Head::ogImage('https://example.com/gallery.jpg', alt: 'Gallery image')
        ->twitterImage('https://example.com/twitter.jpg', alt: 'Twitter image');

    expect(Head::toHtml())
        ->toContain('<meta name="twitter:image" content="https://example.com/twitter.jpg">')
        ->toContain('<meta name="twitter:image:alt" content="Twitter image">')
        ->not->toContain('<meta name="twitter:image" content="https://example.com/gallery.jpg">');

    expect(Head::toArray()['twitter']['image'])->toBe([
        'url' => 'https://example.com/twitter.jpg',
        'alt' => 'Twitter image',
    ]);
});
