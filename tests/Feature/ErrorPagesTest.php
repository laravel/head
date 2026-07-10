<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Laravel\Head\ErrorPages;
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadBuilder;

it('lets error head beat the resolved page head', function (): void {
    Head::defaults(fn (HeadBuilder $head) => $head->title('Acme', suffix: ' - Acme'));

    Head::errors(function (ErrorPages $errors): void {
        $errors->defaults(robots: 'noindex, follow');

        $errors->status(404,
            title: 'Page Not Found',
            description: 'The page could not be found.',
        );
    });

    Route::get('/missing', fn (): string => Head::toHtml(404))->metadata(Head::forMetadata(
        title: 'Original Page',
        description: 'Original description.',
    ));

    $this->get('/missing')
        ->assertOk()
        ->assertSee('<title>Page Not Found - Acme</title>', false)
        ->assertSee('<meta name="description" content="The page could not be found.">', false)
        ->assertSee('<meta name="robots" content="noindex, follow">', false);
});

it('accepts head builder callbacks for error metadata', function (): void {
    Head::defaults(fn (HeadBuilder $head) => $head->title('Acme', suffix: ' - Acme'));

    Head::errors(function (ErrorPages $errors): void {
        $errors->defaults(fn (HeadBuilder $head) => $head->robotsHidden());

        $errors->status(404, fn (HeadBuilder $head) => $head
            ->title('Page Not Found')
            ->description('The page could not be found.'));
    });

    Route::get('/missing', fn (): string => Head::toHtml(404))->metadata(Head::forMetadata(
        title: 'Original Page',
    ));

    $this->get('/missing')
        ->assertOk()
        ->assertSee('<title>Page Not Found - Acme</title>', false)
        ->assertSee('<meta name="description" content="The page could not be found.">', false)
        ->assertSee('<meta name="robots" content="none">', false);
});

it('applies error head when an aborted request renders an error view', function (): void {
    config()->set('view.paths', [__DIR__.'/../Fixtures/views', ...config('view.paths')]);

    Head::defaults(fn (HeadBuilder $head) => $head->title('Acme', suffix: ' - Acme'));

    Head::errors(function (ErrorPages $errors): void {
        $errors->defaults(robots: 'noindex, follow');

        $errors->status(404, title: 'Page Not Found');
    });

    $this->get('/definitely-missing')
        ->assertNotFound()
        ->assertSee('<title>Page Not Found - Acme</title>', false)
        ->assertSee('<meta name="robots" content="noindex, follow">', false);
});
