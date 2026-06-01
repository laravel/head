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
use Laravel\Head\CanonicalMode;
use Laravel\Head\OgType;
use Laravel\Head\TwitterCard;

Head::defaults(function (HeadManager $head) {
    $head
        ->title('Acme', suffix: ' - Acme')
        ->description('Build something great.')
        ->canonical(CanonicalMode::Auto)
        ->og(siteName: 'Acme', type: OgType::Website)
        ->twitter(card: TwitterCard::SummaryLargeImage)
        ->robots('index, follow')
        ->preconnect('https://fonts.example.com');
});
```

The defaults layer is the fallback for the cascade. If no route, controller, or error metadata sets a title, `Acme` renders as-is. When a higher layer sets a page title, the inherited suffix is applied, so `Head::title('About')` renders `About - Acme`. Pass `bare: true` for titles that should ignore the inherited prefix or suffix.

Canonical URLs are rendered automatically unless a later layer calls `Head::canonical(CanonicalMode::None)`. Robots defaults to `index, follow`.

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
Route::withHead(robots: 'noindex, nofollow')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', DashboardController::class)
            ->name('dashboard')
            ->head(title: 'Dashboard');
    });
```

Resource and singleton routes can define metadata too:

```php
Route::resource('posts', PostController::class)->head(
    robots: 'index, follow',
);

Route::singleton('profile', ProfileController::class)->head(
    title: 'Your Profile',
);
```

Route metadata keys mirror the fluent builder methods one-to-one: `title`, `description`, `canonical`, `robots`, `og`, `ogImage`, `ogVideo`, `ogAudio`, `twitter`, `twitterImage`, `preload`, `prefetch`, `preconnect`, `dnsPrefetch`, `alternates`, `feed`, `schema`, `meta`, and `link`. Keys that represent repeatable tags (`ogImage`, `preload`, `feed`, `schema`, …) accept either a single value or a list.

## Controller Metadata

Controllers and actions can override route metadata for dynamic request data:

```php
use Laravel\Head\Facades\Head;
use Laravel\Head\Facades\Schema;
use Laravel\Head\OgType;

Head::title($post->title)
    ->description($post->excerpt)
    ->canonical($post->url())
    ->og(title: $post->title, type: OgType::Article)
    ->ogImage(
        $post->og_image_url,
        alt: $post->title,
        width: 1200,
        height: 630,
    )
    ->schema(
        Schema::article()
            ->headline($post->title)
            ->author(Schema::person()->name($post->author->name))
            ->datePublished($post->published_at)
            ->image($post->og_image_url)
    );
```

For the simplest case — a single OG image with no other knobs — use the `image:` shorthand on `og()`:

```php
Head::og(
    type: OgType::Website,
    title: $page->title,
    description: $page->description,
    image: $page->og_image_url,
);
```

`og(image: ...)` and `ogImage(...)` write to the same underlying image list, so pick whichever reads better at the call site.

## Errors

Error metadata can be registered for status-code-specific pages:

```php
use Laravel\Head\Errors;
use Laravel\Head\Facades\Head;

Head::errors(function (Errors $errors) {
    $errors->defaults(robots: 'noindex, follow');

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

Higher layers replace lower layers field by field. For example, a controller title replaces the route title without replacing the route description. Collection-like values such as performance hints, alternates, feeds, schemas, and Open Graph media are merged and deduplicated by their natural keys, with higher layers winning conflicts. Open Graph and Twitter property bags are merged property by property.

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

For arbitrary tags without a first-class method, use the generic escape hatches:

```php
Head::meta('theme-color', '#000000')
    ->meta('article:author', $post->author->name)
    ->link('manifest', '/manifest.json')
    ->link('apple-touch-icon', '/apple-touch-icon.png', ['sizes' => '180x180']);
```

`meta()` emits `name=` by default and automatically uses `property=` for known RDFa namespaces such as `og:`, `article:`, `book:`, `profile:`, `music:`, `video:`, `fb:`, and `product:`. You may override detection with `property: true` or `property: false`.

### Open Graph And Twitter Media

Repeatable Open Graph media is modeled with explicit top-level methods that take named args directly — no value objects:

```php
use Laravel\Head\OgType;
use Laravel\Head\TwitterCard;

Head::og(type: OgType::Article, title: $post->title)
    ->ogImage($post->hero_image_url)
    ->ogImage(
        $post->gallery_image_url,
        alt: $post->gallery_image_alt,
        width: 1200,
        height: 630,
        type: 'image/jpeg',
    )
    ->twitter(card: TwitterCard::SummaryLargeImage)
    ->twitterImage($post->twitter_image_url, alt: $post->title);
```

`ogImage()`, `ogVideo()`, `ogAudio()`, and `twitterImage()` all accept the same shape: a URL as the first argument plus optional named args for `alt`, `width`, `height`, `type`, and `secureUrl` where the spec defines them. For a single trivial image you can skip the dedicated method entirely and pass `image: 'https://...'` to `og()` or `twitter()`. Use `Head::meta()` for custom Open Graph extensions such as product or article properties.

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

Livewire applications use the same `@head` directive in their document layout:

```blade
<head>
    @head
</head>

<body>
    {{ $slot }}

    @livewireScripts
</body>
```

No Livewire-specific configuration is required. Head data is resolved per request and the resolver is request-scoped, so each `wire:navigate` visit fetches a fresh document whose `@head` reflects the destination route's metadata. Links using `wire:navigate` therefore pick up the next page's route, controller, and error metadata without any component-level head code.
