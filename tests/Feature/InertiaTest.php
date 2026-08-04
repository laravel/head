<?php

declare(strict_types=1);

use Closure;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Ssr\DisablesSsr;
use Inertia\Ssr\ExcludesSsrPaths;
use Inertia\Ssr\Gateway;
use Inertia\Ssr\HasHealthCheck;
use Inertia\Ssr\Response as SsrResponse;
use Inertia\Support\Header;
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadBuilder;
use Laravel\Head\HeadManager;
use LogicException;

it('shares rendered head elements with inertia page objects', function (): void {
    Head::defaults(fn (HeadBuilder $head) => $head->title('Acme', suffix: ' - Acme'));

    Route::get('/dashboard', fn () => Inertia::render('Dashboard', [
        'user' => 'Taylor',
    ]))->withHead(
        title: 'Dashboard',
        description: 'Your application overview.',
    );

    $response = $this->get('/dashboard', [Header::INERTIA => 'true'])
        ->assertOk()
        ->assertJsonPath('component', 'Dashboard')
        ->assertJsonPath('props.user', 'Taylor');

    $elements = $response->json('props.'.HeadManager::INERTIA_PROP);

    expect($elements)->toBeArray()
        ->toContain('<title data-inertia="title">Dashboard - Acme</title>')
        ->toContain('<meta data-inertia="description" name="description" content="Your application overview.">');
});

it('restores shared head elements after inertia flushes its shared props', function (): void {
    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))
        ->withHead(title: 'Dashboard');

    $headers = [Header::INERTIA => 'true'];

    $this->get('/dashboard', $headers)
        ->assertOk()
        ->assertJsonPath(
            'props.'.HeadManager::INERTIA_PROP.'.0',
            '<title data-inertia="title">Dashboard</title>',
        );

    Inertia::flushShared();

    $this->get('/dashboard', $headers)
        ->assertOk()
        ->assertJsonPath(
            'props.'.HeadManager::INERTIA_PROP.'.0',
            '<title data-inertia="title">Dashboard</title>',
        );
});

it('shares stable semantic inertia keys for each head element', function (): void {
    Route::get('/lean', fn () => Inertia::render('Page'))->withHead(
        description: 'Lean page.',
    );

    Route::get('/rich', fn () => Inertia::render('Page'))->withHead(
        title: 'Rich page',
        description: 'Rich page.',
        preload: [['href' => '/fonts/inter.woff2', 'as' => 'font']],
        ogImage: 'https://example.com/rich.jpg',
    );

    $lean = $this->get('/lean', [Header::INERTIA => 'true'])->json('props.'.HeadManager::INERTIA_PROP);
    $rich = $this->get('/rich', [Header::INERTIA => 'true'])->json('props.'.HeadManager::INERTIA_PROP);

    expect($lean)
        ->toContain('<meta data-inertia="description" name="description" content="Lean page.">')
        ->each->toContain(' data-inertia="');

    expect($rich)
        ->toContain('<title data-inertia="title">Rich page</title>')
        ->toContain('<meta data-inertia="description" name="description" content="Rich page.">')
        ->each->toContain(' data-inertia="');
});

it('keeps head elements off the wire during inertia partial reloads', function (): void {
    Route::get('/dashboard', fn () => Inertia::render('Dashboard', [
        'user' => 'Taylor',
    ]))->withHead(title: 'Dashboard');

    $this->get('/dashboard', [
        Header::INERTIA => 'true',
        Header::PARTIAL_COMPONENT => 'Dashboard',
        Header::PARTIAL_ONLY => 'user',
    ])
        ->assertOk()
        ->assertJsonPath('props.user', 'Taylor')
        ->assertJsonMissingPath('props.'.HeadManager::INERTIA_PROP);
});

it('keeps inertia globals out of inertia page objects', function (): void {
    Head::inertiaGlobals(fn (HeadBuilder $head) => $head
        ->viewport('width=device-width, initial-scale=1')
        ->icon('/favicon.svg', type: 'image/svg+xml'));

    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->withHead(
        title: 'Dashboard',
        description: 'Dashboard overview.',
    );

    $elements = $this->get('/dashboard', [Header::INERTIA => 'true'])
        ->assertOk()
        ->json('props.'.HeadManager::INERTIA_PROP);

    expect($elements)
        ->toContain('<title data-inertia="title">Dashboard</title>')
        ->toContain('<meta data-inertia="description" name="description" content="Dashboard overview.">');

    expect(implode("\n", $elements))
        ->not->toContain('data-inertia="viewport"')
        ->not->toContain('data-inertia="link:icon');
});

it('renders inertia globals as static tags and page tags as inertia-managed tags in the inertia root view', function (): void {
    Head::inertiaGlobals(fn (HeadBuilder $head) => $head
        ->viewport('width=device-width, initial-scale=1')
        ->icon('/favicon.svg', type: 'image/svg+xml'));

    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->withHead(
        title: 'Dashboard',
        description: 'Dashboard overview.',
    );

    $this->get('/dashboard')
        ->assertOk()
        ->assertSee('<meta name="viewport" content="width=device-width, initial-scale=1">', false)
        ->assertSee('<link rel="icon" href="/favicon.svg" type="image/svg+xml">', false)
        ->assertSee('<title data-inertia="title">Dashboard</title>', false)
        ->assertSee('<meta data-inertia="description" name="description" content="Dashboard overview.">', false)
        ->assertDontSee('<meta data-inertia="viewport"', false)
        ->assertDontSee('<link data-inertia="link:icon', false);
});

it('renders the full inertia document in the legacy directive root template', function (): void {
    Inertia::setRootView('app-directives');

    Head::inertiaGlobals(fn (HeadBuilder $head) => $head
        ->viewport('width=device-width, initial-scale=1'));

    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->withHead(
        title: 'Dashboard',
        description: 'Dashboard overview.',
    );

    $html = $this->get('/dashboard')->assertOk()->getContent();

    expect(substr_count($html, '<title data-inertia="title">Dashboard</title>'))->toBe(1)
        ->and(substr_count($html, '<meta data-inertia="description" name="description" content="Dashboard overview.">'))->toBe(1)
        ->and($html)->toContain('<meta name="viewport" content="width=device-width, initial-scale=1">');
});

it('preserves the decorated inertia ssr gateway capabilities', function (): void {
    $gateway = new class implements DisablesSsr, ExcludesSsrPaths, Gateway, HasHealthCheck
    {
        public Closure|bool|null $disabled = null;

        /** @var array<int, string> */
        public array $except = [];

        public function dispatch(array $page): ?SsrResponse
        {
            return null;
        }

        public function disable(Closure|bool $condition): void
        {
            $this->disabled = $condition;
        }

        public function except(array|string $paths): void
        {
            $this->except = [
                ...$this->except,
                ...(is_array($paths) ? $paths : [$paths]),
            ];
        }

        public function isHealthy(): bool
        {
            return true;
        }
    };

    $this->app->bind(Gateway::class, fn () => $gateway);

    $decorated = $this->app->make(Gateway::class);
    $condition = fn (): bool => true;

    expect($decorated)
        ->toBeInstanceOf(DisablesSsr::class)
        ->toBeInstanceOf(ExcludesSsrPaths::class)
        ->toBeInstanceOf(HasHealthCheck::class);

    assert($decorated instanceof HasHealthCheck);

    Inertia::disableSsr($condition);
    Inertia::withoutSsr(['/admin', '/internal']);

    expect($gateway->disabled)->toBe($condition)
        ->and($gateway->except)->toBe(['/admin', '/internal'])
        ->and($decorated->isHealthy())->toBeTrue();
});

it('degrades when the decorated inertia ssr gateway lacks capabilities', function (): void {
    $this->app->bind(Gateway::class, fn () => new class implements Gateway
    {
        public function dispatch(array $page): ?SsrResponse
        {
            return null;
        }
    });

    $decorated = $this->app->make(Gateway::class);

    assert($decorated instanceof HasHealthCheck);

    expect(fn () => Inertia::disableSsr())
        ->toThrow(LogicException::class, 'The configured SSR gateway does not support disabling server-side rendering conditionally.')
        ->and(fn () => Inertia::withoutSsr('/admin'))
        ->toThrow(LogicException::class, 'The configured SSR gateway does not support excluding paths from server-side rendering.')
        ->and($decorated->isHealthy())->toBeFalse();
});

it('deduplicates laravel head from the inertia ssr head while preserving other ssr elements', function (): void {
    $this->app->bind(Gateway::class, fn () => new class implements Gateway
    {
        public function dispatch(array $page): ?SsrResponse
        {
            return new SsrResponse(
                implode("\n", [
                    ...$page['props'][HeadManager::INERTIA_PROP],
                    '<meta data-inertia="client-only" name="client-only" content="Preserved">',
                ]),
                '<div id="app">SSR BODY</div>',
            );
        }
    });

    Head::inertiaGlobals(fn (HeadBuilder $head) => $head
        ->viewport('width=device-width, initial-scale=1'));

    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->withHead(
        title: 'Dashboard',
        description: 'Dashboard overview.',
    );

    $html = $this->get('/dashboard')->assertOk()->getContent();

    expect(substr_count($html, '<title data-inertia="title">Dashboard</title>'))->toBe(1)
        ->and(substr_count($html, '<meta data-inertia="description" name="description" content="Dashboard overview.">'))->toBe(1)
        ->and($html)->toContain('<meta name="viewport" content="width=device-width, initial-scale=1">')
        ->and(substr_count($html, '<meta data-inertia="client-only" name="client-only" content="Preserved">'))->toBe(1)
        ->and($html)->toContain('<div id="app">SSR BODY</div>');
});

it('deduplicates the ssr head even when the inertia head component renders before the directive', function (): void {
    $this->app->bind(Gateway::class, fn () => new class implements Gateway
    {
        public function dispatch(array $page): ?SsrResponse
        {
            return new SsrResponse(
                implode("\n", [
                    ...$page['props'][HeadManager::INERTIA_PROP],
                    '<meta data-inertia="client-only" name="client-only" content="Preserved">',
                ]),
                '<div id="app">SSR BODY</div>',
            );
        }
    });

    Inertia::setRootView('app-inertia-head-first');

    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->withHead(
        title: 'Dashboard',
        description: 'Dashboard overview.',
    );

    $html = $this->get('/dashboard')->assertOk()->getContent();

    expect(substr_count($html, '<title data-inertia="title">Dashboard</title>'))->toBe(1)
        ->and(substr_count($html, '<meta data-inertia="description" name="description" content="Dashboard overview.">'))->toBe(1)
        ->and(substr_count($html, '<meta data-inertia="client-only" name="client-only" content="Preserved">'))->toBe(1)
        ->and($html)->toContain('<div id="app">SSR BODY</div>');
});

it('renders inertia-managed tags when the directive runs outside the root view scope', function (): void {
    Inertia::setRootView('app-nested');

    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->withHead(
        title: 'Dashboard',
    );

    $this->get('/dashboard')
        ->assertOk()
        ->assertSee('<title data-inertia="title">Dashboard</title>', false);
});
