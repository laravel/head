<p align="center">
<a href="https://github.com/laravel/head/actions"><img src="https://github.com/laravel/head/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/head"><img src="https://img.shields.io/packagist/dt/laravel/head" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/head"><img src="https://img.shields.io/packagist/v/laravel/head" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/head"><img src="https://img.shields.io/packagist/l/laravel/head" alt="License"></a>
</p>

## Introduction

Laravel Head provides a fluent API for managing your application's document `<head>`, with support for title and meta tags, Open Graph and Twitter cards, canonical URLs, robots directives, performance hints, and structured data. It works across Blade, Livewire, and Inertia.

## Installation

```bash
composer require laravel/head
```

## Resolution Precedence

Head data resolves from five layers, listed here from lowest to highest priority:

1. Global defaults
2. Route group metadata
3. Route metadata
4. Runtime metadata
5. Error metadata

Higher layers replace lower layers field by field. For example, a runtime title replaces the route title without replacing the route description. The sections that follow describe how to set metadata at each layer. See [Rendering](#rendering) for how the resolved result is emitted in Blade, Livewire, and Inertia.

## Defaults

Register global defaults in a service provider:

```php
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadManager;
use Laravel\Head\Enums\OgType;
use Laravel\Head\Enums\RobotsRule;
use Laravel\Head\Enums\TwitterCard;

Head::defaults(function (HeadManager $head) {
    $head
        ->title('Acme', suffix: ' - Acme')
        ->description('Build something great.')
        ->applicationName('Acme')
        ->themeColor('#0f172a')
        ->colorScheme('light dark')
        ->canonical()
        ->og(siteName: 'Acme', type: OgType::Website)
        ->twitter(card: TwitterCard::SummaryLargeImage)
        ->robots(RobotsRule::All)
        ->preconnect('https://fonts.example.com');
});
```

The defaults layer is the lowest-priority layer. If no route, runtime, or error metadata sets a title, `Acme` renders as-is. When a higher layer sets a page title, the inherited suffix is applied, so `Head::title('About')` renders `About - Acme`. Pass `exact: true` for titles that should ignore the inherited prefix or suffix.

Canonical URLs are rendered when you call `Head::canonical()`, by using the current request URL. To set an explicit URL you may pass a string `Head::canonical('/about')`. A later layer can remove an inherited canonical URL with `Head::canonical(false)`.

Robots directives may be passed as a raw string, as `RobotsRule` enum cases, or as a list mixing both forms. Lists are rendered as comma-separated directives, so `Head::robots([RobotsRule::NoIndex, RobotsRule::NoFollow])` renders `noindex, nofollow`.

## Route Metadata

Many pages can define their metadata directly on the route, especially semi-static pages whose metadata is known ahead of time.

### Routes & Groups

```php
Route::view('/contact', 'contact')
    ->name('contact')
    ->withHead(
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
            ->withHead(title: 'Dashboard');
    });
```

Resource and singleton routes can define metadata too:

```php
Route::resource('posts', PostController::class)->withHead(
    robots: 'index, follow',
);

Route::singleton('profile', ProfileController::class)->withHead(
    title: 'Your Profile',
);
```

### Supported Properties

The properties you pass to `->withHead()` match the fluent builder methods:

| Category | Properties |
| --- | --- |
| Document | `title`, `description`, `canonical`, `robots` |
| App metadata | `themeColor`, `applicationName`, `colorScheme`, `referrer`, `viewport` |
| Social | `og`, `ogImage`, `ogVideo`, `ogAudio`, `twitter`, `twitterImage` |
| Performance | `preload`, `prefetch`, `preconnect`, `dnsPrefetch` |
| Discovery | `alternates`, `feed`, `icon`, `appleTouchIcon`, `maskIcon`, `manifest` |
| Structured data | `schema` |
| Custom tags | `meta`, `link` |

Nested option names use the same camel-case names as the fluent API, such as `forceHttps`, `siteName`, and `secureUrl`.

Repeatable properties, such as `ogImage`, `preload`, `feed`, `schema`, and `icon`, accept either a single value or a list.

### Dynamic Metadata

When a value isn't known until a request arrives, such as the title of the post being viewed, you may pass a closure instead of named arguments:

```php
use Illuminate\Routing\Route;

Route::get('/posts/{post}', ShowPostController::class)
    ->withHead(fn (Route $route) => [
        'title' => $route->parameter('post')->title,
    ]);
```

> [!NOTE]
> Route metadata closures are not compatible with cached routes. If you cache your routes, set request-dependent metadata at runtime instead.

## Runtime Metadata

Runtime calls to the `Head` facade override route metadata for request dependent data. Controllers and actions are the most common place to set this data:

```php
use App\Models\Post;
use Laravel\Head\Facades\Head;

public function show(Post $post)
{
    Head::title($post->title)
        ->description($post->description);

    return view('posts.show', ['post' => $post]);
}
```

Multiple runtime calls are merged in the order they run. For single-value fields like title, description, canonical URL, and robots directives, the later call wins. Repeatable fields like images, performance links, feeds, schemas, generic meta tags, and generic links accumulate or replace existing entries by their internal key.

## Error Pages

Error metadata can be registered for status-code-specific pages:

```php
use Laravel\Head\ErrorPages;
use Laravel\Head\Facades\Head;

Head::errors(function (ErrorPages $errors) {
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
use Laravel\Head\Enums\OgType;
use Laravel\Head\Enums\TwitterCard;

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

> [!NOTE]
> Document `title` and `description` automatically fill missing `og:title`, `og:description`, `twitter:title`, and `twitter:description` values. If no Twitter image is set, the first Open Graph image is also used for `twitter:image`.

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

## Theme Colors

Theme colors can be set globally, per route, or at runtime:

```php
Head::themeColor('#0f172a');
```

This renders a `<meta name="theme-color">` tag. If you need media-specific theme colors, use `meta()` directly:

```php
Head::meta('theme-color', '#ffffff', media: '(prefers-color-scheme: light)')
    ->meta('theme-color', '#111827', media: '(prefers-color-scheme: dark)');
```

Route metadata supports simple theme colors through the same camel-case key:

```php
Route::view('/dashboard', 'dashboard')->withHead(
    themeColor: '#0f172a',
);
```

## App Metadata & Icons

Laravel Head includes helpers for common browser and application metadata:

```php
Head::applicationName('Acme')
    ->colorScheme('light dark')
    ->referrer('strict-origin-when-cross-origin')
    ->viewport('width=device-width, initial-scale=1')
    ->icon('/favicon.svg', type: 'image/svg+xml')
    ->icon('/favicon-32x32.png', type: 'image/png', sizes: '32x32')
    ->appleTouchIcon('/apple-touch-icon.png', sizes: '180x180')
    ->maskIcon('/safari-pinned-tab.svg', color: '#111827')
    ->manifest('/site.webmanifest');
```

Route metadata uses the same names:

```php
Route::view('/dashboard', 'dashboard')->withHead(
    applicationName: 'Acme',
    colorScheme: 'light dark',
    icon: [
        ['href' => '/favicon.svg', 'type' => 'image/svg+xml'],
        ['href' => '/favicon-32x32.png', 'type' => 'image/png', 'sizes' => '32x32'],
    ],
    appleTouchIcon: ['href' => '/apple-touch-icon.png', 'sizes' => '180x180'],
    manifest: '/site.webmanifest',
);
```

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
Head::meta('format-detection', 'telephone=no')
    ->meta('article:author', $post->author->name)
    ->link('search', '/opensearch.xml', [
        'type' => 'application/opensearchdescription+xml',
        'title' => 'Acme Search',
    ])
    ->link('me', 'https://social.example.com/@acme');
```

Meta tags may include a media query when the browser should only apply the tag under matching conditions:

```php
Head::meta('theme-color', '#ffffff', media: '(prefers-color-scheme: light)')
    ->meta('theme-color', '#111827', media: '(prefers-color-scheme: dark)');
```

`meta()` emits `name=` by default and automatically uses `property=` for known RDFa namespaces such as `og:`, `article:`, `book:`, `profile:`, `music:`, `video:`, `fb:`, and `product:`. You may override detection with `property: true` or `property: false`.

## Schemas

Built-in schema builders cover the common JSON-LD types:

```php
use Laravel\Head\Enums\OfferAvailability;
use Laravel\Head\Facades\Schema;

Head::schema(
    Schema::product()
        ->name($product->name)
        ->offers(
            Schema::offer()
                ->price($product->price)
                ->priceCurrency('USD')
                ->availability(OfferAvailability::InStock)
        )
);
```

The built-in factory methods are `article`, `blogPosting`, `product`, `offer`, `breadcrumbs`, `faq`, `organization`, `person`, `webPage`, and `webSite`. Unknown factory methods fall back to a generic schema object so custom schema.org types can still be expressed.

Invalid JSON-LD schema data throws outside production and is logged as a warning in production.

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

The HTML renderer powers the `@head` directive and the rendered elements Laravel Head shares with Inertia as the `head` prop. The head array renderer powers `Head::toArray()` for applications that want the resolved head as structured data.

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

No Livewire-specific configuration is required. Head data is resolved per request and the resolver is request-scoped, so each `wire:navigate` visit fetches a fresh document whose `@head` reflects the destination route's metadata. Links using `wire:navigate` therefore pick up the next page's route, runtime, and error metadata without any component-level head code.

### Inertia

When Inertia is installed, Laravel Head automatically shares the resolved head as an array of rendered element strings under a `head` prop on every page object:

```json
{
    "props": {
        "head": [
            "<title data-inertia=\"title\">Dashboard - Acme</title>",
            "<meta data-inertia=\"description\" name=\"description\" content=\"Your application overview.\">"
        ]
    }
}
```

Inertia renders these elements into the document head for you — enable it with the `serverHead` option in your application's entry point:

```js
createInertiaApp({
    // ...
    serverHead: true,
})
```

The elements are rendered during server-side rendering, hydrated on the client, and kept in sync on every Inertia visit. No client-side `<Head>` component or template is required.

The prop is not sent during partial reloads; the client keeps the head from the last full visit. If your application already uses the `head` prop for something else, publish the configuration file and change the prop name:

```shell
php artisan vendor:publish --tag=head-config
```

```php
'inertia' => [
    'prop' => '_head',
],
```

Then point Inertia at the same prop with `serverHead: '_head'`.

Applications that want the resolved head as structured data (titles, Open Graph values, JSON-LD schemas, etc.) rather than rendered tags can still call `Head::toArray()`.

## Security Vulnerabilities

Please review [our security policy](https://github.com/laravel/head/security/policy) on how to report security vulnerabilities.

## License

Laravel Head is open-sourced software licensed under the [MIT license](LICENSE.md).
