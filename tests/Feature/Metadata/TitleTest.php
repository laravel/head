<?php

declare(strict_types=1);

use Laravel\Head\Facades\Head;

it('renders the default title without applying its inherited suffix', function (): void {
    Head::defaults(fn (Laravel\Head\Head $head): Laravel\Head\Head => $head->title('Acme', suffix: ' - Acme'));

    expect(Head::render())->toContain('<title>Acme</title>');
});

it('can render a bare title without the configured suffix', function (): void {
    Head::defaults(fn (Laravel\Head\Head $head): Laravel\Head\Head => $head->title('Acme', suffix: ' - Acme'));

    Head::title('Checkout', bare: true);

    expect(Head::render())->toContain('<title>Checkout</title>');
});
