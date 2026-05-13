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

## Usage

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
        ->robots()->index()->follow();
});
```

Define page metadata next to the route:

```php
Route::view('/contact', 'contact')
    ->name('contact')
    ->head(
        title: 'Contact Us',
        description: 'Get in touch.',
    );

Route::withHead(robots: ['noindex', 'nofollow'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/dashboard', DashboardController::class)
            ->head(title: 'Dashboard');
    });
```

Override route metadata from a controller:

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

Render the accumulated tags from your Blade layout:

```blade
<head>
    <meta charset="utf-8">
    @head
</head>
```

Register error metadata for SEO-safe error pages:

```php
use Laravel\Head\Errors;

Head::errors(function (Errors $errors) {
    $errors->defaults(robots: ['noindex', 'follow']);

    $errors->status(404,
        title: 'Page Not Found',
        description: 'The page you are looking for could not be found.',
    );
});
```

Laravel Head also renders performance hints, pagination links, locale alternates, feed discovery, and JSON-LD schema objects:

```php
Head::preload(asset('fonts/inter.woff2'), as: 'font', crossorigin: true)
    ->prefetch(asset('images/next.webp'))
    ->preconnect('https://cdn.example.com')
    ->dnsPrefetch('https://analytics.example.com')
    ->paginate($posts)
    ->alternates([
        'en' => 'https://example.com/en/about',
        'fr' => 'https://example.com/fr/about',
    ])
    ->feed('/feed', title: 'Acme RSS');
```

Custom schema types can be registered explicitly:

```php
use Laravel\Head\Schema\SchemaObject;
use Laravel\Head\SchemaType;

#[SchemaType('JobPosting')]
class JobPosting extends SchemaObject
{
    public function title(string $title): static
    {
        return $this->set('title', $title);
    }
}

Schema::register(JobPosting::class);

Head::schema(Schema::jobPosting()->title('Senior Laravel Developer'));
```

Invalid JSON-LD payloads throw outside production and are logged as warnings in production.

When Inertia is installed, Laravel Head shares the resolved head payload as a lazy `head` prop on every page object.
