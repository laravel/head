<?php

declare(strict_types=1);

use Laravel\Head\Facades\Head;

it('does not render robots tags by default', function (): void {
    expect(Head::toHtml())->not->toContain('name="robots"');
});
