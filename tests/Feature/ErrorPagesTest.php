<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Laravel\Head\ErrorPages;
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadManager;

it('lets error head beat the resolved page head', function (): void {
    Head::defaults(fn (HeadManager $head) => $head->title('Acme', suffix: ' - Acme'));

    Head::errors(function (ErrorPages $errors): void {
        $errors->defaults(robots: 'noindex, follow');

        $errors->status(404,
            title: 'Page Not Found',
            description: 'The page could not be found.',
        );
    });

    Route::get('/missing', fn (): string => Head::toHtml(404))->withHead(
        title: 'Original Page',
        description: 'Original description.',
    );

    $this->get('/missing')
        ->assertOk()
        ->assertSee('<title>Page Not Found - Acme</title>', false)
        ->assertSee('<meta name="description" content="The page could not be found.">', false)
        ->assertSee('<meta name="robots" content="noindex, follow">', false);
});
