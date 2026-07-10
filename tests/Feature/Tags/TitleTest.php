<?php

declare(strict_types=1);

use Laravel\Head\Facades\Head;
use Laravel\Head\HeadBuilder;

it('renders the default title without applying its inherited suffix', function (): void {
    Head::defaults(fn (HeadBuilder $head): HeadBuilder => $head->title('Acme', suffix: ' - Acme'));

    expect(Head::toHtml())->toContain('<title>Acme</title>');
});

it('can render an exact title without the configured suffix', function (): void {
    Head::defaults(fn (HeadBuilder $head): HeadBuilder => $head->title('Acme', suffix: ' - Acme'));

    Head::title('Checkout', exact: true);

    expect(Head::toHtml())->toContain('<title>Checkout</title>');
});
