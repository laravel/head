<p align="center">
<a href="https://github.com/laravel/head/actions"><img src="https://github.com/laravel/head/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/head"><img src="https://img.shields.io/packagist/dt/laravel/head" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/head"><img src="https://img.shields.io/packagist/v/laravel/head" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/head"><img src="https://img.shields.io/packagist/l/laravel/head" alt="License"></a>
</p>

## Introduction

Laravel Head provides first-party head management for Laravel applications, covering metadata, structured data, and performance hints across Blade, Livewire, and Inertia.

## Installation

```bash
composer require laravel/head
```

Laravel Head requires PHP 8.3 or later and supports Laravel 12 and Laravel 13.

## Rendering

Render the accumulated tags from your Blade layout with the `@head` directive:

```blade
<head>
    <meta charset="utf-8">
    @head
</head>
```

`@head` renders synchronously. Define page metadata before the layout is rendered.

## Defaults

Register global defaults in a service provider:

```php
use Laravel\Head\Facades\Head;
use Laravel\Head\Head as HeadManager;

Head::defaults(function (HeadManager $head) {
    $head
        ->title()->suffix(' - Acme')->fallback('Acme')
        ->description('Build something great.')
        ->canonical()->auto()
        ->og()->siteName('Acme')->type('website')
        ->twitter()->card('summary_large_image')
        ->robots()->index()->follow()
        ->preconnect('https://fonts.example.com');
});
```

Title suffixes are appended unless a later layer calls `->title()->bare()`. Canonical URLs are rendered automatically unless a later layer calls `->canonical()->none()`. Robots defaults to `index, follow`.

## Route Metadata

Most pages can define their metadata directly on the route:

```php
Route::view('/contact', 'contact')
    ->name('contact')
    ->head(
        title: 'Contact Us',
        description: 'Get in touch.',
    );

Route::get('/posts/{post}', ShowPostController::class)
    ->head(fn (Route $route) => [
        'title' => $route->parameter('post')->title,
    ]);
```

Shared route metadata can be applied to a group:

```php
Route::withHead(robots: ['noindex', 'nofollow'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', DashboardController::class)
            ->name('dashboard')
            ->head(title: 'Dashboard');
    });
```

Route metadata supports the same common keys as the fluent builder, including `title`, `description`, `canonical`, `robots`, `og`, `twitter`, `preload`, `prefetch`, `preconnect`, `dnsPrefetch`, `alternates`, `feeds`, and `schemas`.

## Controller Metadata

Controllers and actions can override route metadata for dynamic request data:

```php
use Laravel\Head\Facades\Head;
use Laravel\Head\Facades\Schema;

Head::title($post->title)
    ->description($post->excerpt)
    ->canonical($post->url())
    ->og()->title($post->title)->image($post->og_image_url)->type('article')
    ->schema(
        Schema::article()
            ->headline($post->title)
            ->author(Schema::person()->name($post->author->name))
            ->datePublished($post->published_at)
            ->image($post->og_image_url)
    );
```

## Errors

Error metadata can be registered for status-code-specific pages:

```php
use Laravel\Head\Errors;
use Laravel\Head\Facades\Head;

Head::errors(function (Errors $errors) {
    $errors->defaults(robots: ['noindex', 'follow']);

    $errors->status(404,
        title: 'Page Not Found',
        description: 'The page you are looking for could not be found.',
    );
});
```

When a response is rendered for a registered error status, that metadata beats every other layer.

## Cascade

Head data resolves in this order, from lowest to highest priority:

1. Global defaults
2. Route group metadata
3. Route metadata
4. Controller or action metadata
5. Error metadata

Higher layers replace lower layers field by field. For example, a controller title replaces the route title without replacing the route description. Collection-like values such as performance hints, alternates, feeds, and schemas are merged and deduplicated by their natural keys, with higher layers winning conflicts.

## Performance And Discovery

Laravel Head renders performance hints, pagination links, locale alternates, and feed discovery:

```php
Head::preload(asset('fonts/inter.woff2'), as: 'font', crossorigin: true)
    ->prefetch(asset('images/next.webp'))
    ->preconnect('https://cdn.example.com')
    ->dnsPrefetch('https://analytics.example.com')
    ->paginate($posts)
    ->alternates([
        'en' => 'https://example.com/en/about',
        'fr' => 'https://example.com/fr/about',
        'x-default' => 'https://example.com/about',
    ])
    ->feed('/feed', title: 'Acme RSS')
    ->feed('/feed.atom', type: 'atom', title: 'Acme Atom');
```

## Schemas

Built-in schema builders cover the common high-value JSON-LD types:

```php
Head::schema(
    Schema::product()
        ->name($product->name)
        ->offers(
            Schema::offer()
                ->price($product->price)
                ->priceCurrency('USD')
                ->availability('InStock')
        )
);
```

The built-in factory methods are `article`, `blogPosting`, `product`, `offer`, `breadcrumbs`, `faq`, `organization`, `person`, `webPage`, and `webSite`. Unknown factory methods fall back to a generic schema object so custom schema.org types can still be expressed.

Invalid JSON-LD payloads throw outside production and are logged as warnings in production.

### Custom Schemas

Custom schema types can be registered explicitly:

```php
use DateTimeInterface;
use Laravel\Head\Facades\Schema;
use Laravel\Head\Schema\SchemaObject;
use Laravel\Head\SchemaType;

#[SchemaType('JobPosting')]
class JobPosting extends SchemaObject
{
    public function title(string $title): static
    {
        return $this->set('title', $title);
    }

    public function datePosted(DateTimeInterface|string $date): static
    {
        return $this->date('datePosted', $date);
    }
}

Schema::register(JobPosting::class);

Head::schema(
    Schema::jobPosting()
        ->title('Senior Laravel Developer')
        ->datePosted(now())
);
```

## Inertia

When Inertia is installed, Laravel Head automatically shares the resolved head payload as a `head` prop on every page object:

```json
{
    "props": {
        "head": {
            "title": "Dashboard - Acme",
            "description": "Your application overview."
        }
    }
}
```

The `head` prop is shared as an always-included Inertia prop, so it is still present during partial reloads.

## Livewire

Livewire applications can use the same `@head` directive in their document layout. During `wire:navigate` visits, Livewire requests the next document and Laravel Head resolves metadata for that route before the response is returned:

```blade
<head>
    @head
</head>

<body>
    {{ $slot }}

    @livewireScripts
</body>
```

Links using `wire:navigate` receive the next page's route, controller, and error metadata without component-level head code.
