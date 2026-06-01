<p align="center">
<a href="https://github.com/laravel/head/actions"><img src="https://github.com/laravel/head/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/head"><img src="https://img.shields.io/packagist/dt/laravel/head" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/head"><img src="https://img.shields.io/packagist/v/laravel/head" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/head"><img src="https://img.shields.io/packagist/l/laravel/head" alt="License"></a>
</p>

## Introduction

Laravel Head is a head tag manager for Laravel applications. You fluently describe metadata in layers (global defaults, routes, controllers, and error pages), and Laravel Head resolves them into a single set of tags that render in your Blade, Livewire, or Inertia views.

## Installation

```bash
composer require laravel/head
```

## Resolution Precedence

Head data resolves from five layers, listed here from lowest to highest priority:

1. Global defaults
2. Route group metadata
3. Route metadata
4. Controller or action metadata
5. Error metadata

Higher layers replace lower layers field by field. For example, a controller title replaces the route title without replacing the route description. The sections that follow describe how to set metadata at each layer. See [Rendering](#rendering) for how the resolved result is emitted in Blade, Livewire, and Inertia.

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

The defaults layer is the lowest-priority layer. If no route, controller, or error metadata sets a title, `Acme` renders as-is. When a higher layer sets a page title, the inherited suffix is applied, so `Head::title('About')` renders `About - Acme`. Pass `bare: true` for titles that should ignore the inherited prefix or suffix.

Canonical URLs are rendered automatically unless a later layer calls `Head::canonical(CanonicalMode::None)`. Robots defaults to `index, follow`.

## Route Metadata
Many pages can define their metadata directly on the route, especially semi static pages whose metadata is known ahead of time.

```php
Route::view('/contact', 'contact')
    ->name('contact')
    ->head(
        title: 'Contact Us',
        description: 'Get in touch.',
    );
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

The keys you pass to `->head()` match the fluent builder methods: `title`, `description`, `canonical`, `robots`, `og`, `ogImage`, `ogVideo`, `ogAudio`, `twitter`, `twitterImage`, `preload`, `prefetch`, `preconnect`, `dnsPrefetch`, `alternates`, `feed`, `schema`, `meta`, and `link`. Keys for repeatable tags (`ogImage`, `preload`, `feed`, `schema`, …) accept either a single value or a list. The one builder method without a route key is `paginate()`, whose prev/next links come from a live paginator you resolve in the controller.

When a value isn't known until a request arrives, such as the title of the post being viewed, you may pass a closure instead of named arguments. The closure receives the matched route so you can read its parameters, and returns an array using those same keys:

```php
Route::get('/posts/{post}', ShowPostController::class)
    ->head(fn (Route $route) => [
        'title' => $route->parameter('post')->title,
    ]);
```

> [!NOTE]
> A closure can't be serialized, so a route that uses this form makes `php artisan route:cache` fail. If you cache your routes, set request-dependent metadata from the [controller](#controller-metadata) instead.

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

## Open Graph & Twitter

Open Graph and Twitter card properties are set with `og()` and `twitter()`. Repeatable media is added through top-level methods that take named arguments directly:

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

`ogImage()`, `ogVideo()`, `ogAudio()`, and `twitterImage()` all accept the same shape: a URL as the first argument plus optional named args for `alt`, `width`, `height`, `type`, and `secureUrl` where the spec defines them.

For a single OG image with no other attributes, pass the `image:` shorthand to `og()` or `twitter()`:

```php
Head::og(
    type: OgType::Website,
    title: $page->title,
    description: $page->description,
    image: $page->og_image_url,
);
```

`og(image: ...)` and `ogImage(...)` write to the same underlying image list, so pick whichever reads better at the call site. Use [`meta()`](#custom-tags) for custom Open Graph extensions such as product or article properties.

## Performance & Discovery

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

## Custom Tags

For tags without a dedicated method, use `meta()` and `link()`:

```php
Head::meta('theme-color', '#000000')
    ->meta('article:author', $post->author->name)
    ->link('manifest', '/manifest.json')
    ->link('apple-touch-icon', '/apple-touch-icon.png', ['sizes' => '180x180']);
```

`meta()` emits `name=` by default and automatically uses `property=` for known RDFa namespaces such as `og:`, `article:`, `book:`, `profile:`, `music:`, `video:`, `fb:`, and `product:`. You may override detection with `property: true` or `property: false`.

## Schemas

Built-in schema builders cover the common JSON-LD types:

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

## Rendering

Laravel Head resolves these layers into a single set of tags. Where that result is emitted depends on your stack.

### Blade

Render the accumulated tags in your layout's `<head>` with the `@head` directive:

```blade
<head>
    <meta charset="utf-8">
    @head
</head>
```

`@head` renders synchronously, so define page metadata before the layout is rendered.

### Livewire

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

### Inertia

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

> [!IMPORTANT]
> TODO: Document rendering the shared payload into the document `<head>` on the client.

## Security Vulnerabilities

Please review [our security policy](https://github.com/laravel/head/security/policy) on how to report security vulnerabilities.

## License

Laravel Head is open-sourced software licensed under the [MIT license](LICENSE.md).
