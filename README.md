<p align="center">
<a href="https://github.com/laravel/head/actions"><img src="https://github.com/laravel/head/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/head"><img src="https://img.shields.io/packagist/dt/laravel/head" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/head"><img src="https://img.shields.io/packagist/v/laravel/head" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/head"><img src="https://img.shields.io/packagist/l/laravel/head" alt="License"></a>
</p>

## Introduction

Laravel Head provides a fluent API for managing your application's document `<head>`, with support for title and meta tags, Open Graph, canonical URLs, robots directives, performance hints, and structured data. It works across Blade, Livewire, and Inertia.

## Installation

```bash
composer require laravel/head
```

## Resolution Precedence

Page head data resolves from five layers, listed here from lowest to highest priority:

1. Page defaults
2. Route group metadata
3. Route metadata
4. Runtime metadata
5. Error metadata

Higher layers replace lower layers field by field. For example, a runtime title replaces the route title without replacing the route description. The sections that follow describe how to set metadata at each layer. See [Rendering](#rendering) for how the resolved result is emitted in Blade, Livewire, and Inertia.

## Defaults

Register page defaults in a service provider:

```php
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadBuilder;
use Laravel\Head\Enums\OgType;

Head::defaults(function (HeadBuilder $head) {
    $head
        ->title('Acme', suffix: ' - Acme')
        ->description('Build something great.')
        ->canonical()
        ->og(siteName: 'Acme', type: OgType::Website)
        ->robotsSearchable()
        ->preconnect('https://fonts.example.com');
});
```

The defaults layer is the lowest-priority page layer. If no route, runtime, or error metadata sets a title, `Acme` renders as-is. When a higher layer sets a page title, the inherited suffix is applied, so `Head::title('About')` renders `About - Acme`. Pass `exact: true` for titles that should ignore the inherited prefix or suffix.

Canonical URLs are rendered when you call `Head::canonical()`, by using the current request URL. To set an explicit URL you may pass a string, such as `Head::canonical('/about')`. Canonical URLs are normalized to `https` by default; pass `forceHttps: false` to preserve the request scheme.

Robots directives may be passed as a raw string, as `RobotsRule` enum cases, or as a list mixing both forms. Lists are rendered as comma-separated directives, so `Head::robots([RobotsRule::NoIndex, RobotsRule::NoFollow])` renders `noindex, nofollow`.

For the two common intents, `Head::robotsSearchable()` renders `all`, and `Head::robotsHidden()` renders `none`.

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

Shared route metadata can be applied to a group, at any position in the chain:

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

`withHead()` stores plain arrays through Laravel's native route metadata API, equivalent to calling `->metadata()` with the attributes nested under a `head` key, so route metadata remains compatible with cached routes.

The named arguments are intentionally limited to Laravel Head's built-in route properties so editors and static analysis can catch misspelled names. Route attributes registered by custom tag builders may be passed through `extensions`:

```php
Route::get('/article', ArticleController::class)->withHead(
    title: 'Article',
    extensions: ['readingTime' => 4],
);
```

### Supported Properties

The supported route properties map to the same names as the fluent builder methods:

| Category | Properties |
| --- | --- |
| Document | `title`, `description`, `canonical`, `robots` |
| App metadata | `themeColor`, `applicationName`, `colorScheme`, `referrer`, `viewport`, `appleWebAppTitle`, `webAppCapable`, `appleWebAppStatusBarStyle` |
| Social | `og`, `ogImage`, `ogVideo`, `ogAudio` |
| Performance | `preload`, `prefetch`, `preconnect`, `dnsPrefetch` |
| Discovery | `alternates`, `feed`, `icon`, `favicon`, `appleTouchIcon`, `appleTouchStartupImage`, `maskIcon`, `manifest` |
| Structured data | `schema` |
| Custom tags | `meta`, `link` |

Nested option names use the same camel-case names as the fluent API, such as `forceHttps`, `siteName`, and `secureUrl`.

Repeatable properties, such as `ogImage`, `preload`, `feed`, `schema`, `icon`, and `appleTouchStartupImage`, accept either a single value or a list.

### Request Metadata

When a value isn't known until a request arrives, such as the title of the post being viewed, set it at runtime instead:

```php
use Laravel\Head\Facades\Head;

public function __invoke(Post $post): Response
{
    Head::title($post->title);

    // ...
}
```

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

Multiple runtime calls are merged in the order they run. For single-value fields like title, description, canonical URL, and robots directives, the later call wins. Repeatable fields keep multiple entries, but adding the same key again updates the earlier entry. For `ogImage()`, the URL is the key:

```php
Head::ogImage('/images/cover.jpg', alt: 'Draft cover')
    ->ogImage('/images/gallery.jpg', alt: 'Gallery image')
    ->ogImage('/images/cover.jpg', alt: 'Final cover', width: 1200, height: 630);
```

```html
<meta property="og:image" content="/images/cover.jpg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="Final cover">
<meta property="og:image" content="/images/gallery.jpg">
<meta property="og:image:alt" content="Gallery image">
```

Open Graph media inherited from your defaults acts as a fallback. When route, runtime, or error metadata defines its own media of the same type, the default media is replaced instead of merged, so a page's `og:image` always wins over a site-wide default image.

Conditional metadata may be defined fluently with `when()` and `unless()`:

```php
Head::title($post->title)
    ->when($post->isDraft(), fn ($head) => $head->robotsHidden());
```

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

Both `defaults()` and `status()` also accept the same fluent builder callback used by `Head::defaults()`:

```php
use Laravel\Head\ErrorPages;
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadBuilder;

Head::errors(function (ErrorPages $errors) {
    $errors->status(404, fn (HeadBuilder $head) => $head
        ->title('Page Not Found')
        ->description('The page you are looking for could not be found.'));
});
```

When a response is rendered for a registered error status, that metadata beats every other layer.

Status detection is automatic when Laravel renders an error view and for respond-phase hooks such as Inertia's `handleExceptionsUsing()`. If you render an error response inside an `$exceptions->render()` callback, call `Head::status(404)` before rendering so the error metadata is applied.

## Open Graph

Open Graph properties are set with `og()`. Repeatable media is added through top-level methods that take named arguments directly:

```php
use Laravel\Head\Enums\ImageType;
use Laravel\Head\Enums\OgType;

Head::og(type: OgType::Article, title: $post->title)
    ->ogImage($post->hero_image_url)
    ->ogImage(
        $post->gallery_image_url,
        alt: $post->gallery_image_alt,
        width: 1200,
        height: 630,
        type: ImageType::Jpeg,
    );
```

`ogImage()`, `ogVideo()`, and `ogAudio()` all accept the same shape: a URL as the first argument plus optional named args for `alt`, `width`, `height`, `type`, and `secureUrl` where the spec defines them.

Image MIME types can be passed as `ImageType` enum cases anywhere the API accepts an image `type`, such as `ImageType::Svg`, `ImageType::Png`, `ImageType::Jpeg`, and `ImageType::Webp`.

> [!NOTE]
> Document `title` and `description` automatically fill missing `og:title` and `og:description` values.

For a single OG image with no other attributes, pass the `image:` shorthand to `og()`:

```php
Head::og(
    type: OgType::Website,
    title: $page->title,
    description: $page->description,
    image: $page->og_image_url,
);
```

`og(image: ...)` and `ogImage(...)` write to the same underlying image list, so pick whichever reads better at the call site. Use [`meta()`](#custom-tags) for custom Open Graph extensions such as product or article properties.

### Twitter Cards

To render X/Twitter cards from the same title, description, and image used by Open Graph, register `twitter()` in your defaults:

```php
use Laravel\Head\Enums\TwitterCard;
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadBuilder;

Head::defaults(fn (HeadBuilder $head) => $head->twitter(
    card: TwitterCard::SummaryLargeImage,
));
```

Then page level metadata like this:

```php
Head::title('Introducing Laravel Head')
    ->description('A fluent API for Laravel document head metadata.')
    ->ogImage('https://example.com/social.jpg', alt: 'Introducing Laravel Head');
```

Will render matching Twitter tags:

```html
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Introducing Laravel Head">
<meta name="twitter:description" content="A fluent API for Laravel document head metadata.">
<meta name="twitter:image" content="https://example.com/social.jpg">
<meta name="twitter:image:alt" content="Introducing Laravel Head">
```

You may customize individual pages with explicit Twitter values:

```php
Head::twitter(title: $post->social_title)
    ->twitterImage($post->social_image_url, alt: $post->title);
```

Route metadata accepts `twitter` and `twitterImage`.

## Theme Colors

Theme colors can be set globally, per route, or at runtime:

```php
Head::themeColor('#0f172a');
```

This renders a `<meta name="theme-color">` tag. Media-specific theme colors can use the `Media` enum:

```php
use Laravel\Head\Enums\Media;

Head::themeColor('#ffffff', media: Media::Light)
    ->themeColor('#111827', media: Media::Dark);
```

`Media` also includes `Portrait` and `Landscape`. Any media parameter continues to accept a custom media query string.

Route metadata supports simple theme colors through the same camel-case key:

```php
Route::view('/dashboard', 'dashboard')->withHead(
    themeColor: '#0f172a',
);
```

## App Metadata & Icons

Laravel Head includes helpers for common browser and application metadata:

```php
use Laravel\Head\Enums\ImageType;
use Laravel\Head\Enums\Media;

Head::applicationName('Acme')
    ->colorScheme('light dark')
    ->referrer('strict-origin-when-cross-origin')
    ->viewport('width=device-width, initial-scale=1')
    ->appleWebAppTitle('Acme')
    ->webAppCapable()
    ->appleWebAppStatusBarStyle('black')
    ->favicon('/favicon.svg', type: ImageType::Svg)
    ->icon('/favicon-32x32.png', type: ImageType::Png, sizes: '32x32')
    ->appleTouchIcon('/apple-touch-icon.png', sizes: '180x180')
    ->appleTouchStartupImage('/launch.png', media: Media::Portrait)
    ->maskIcon('/safari-pinned-tab.svg', color: '#111827')
    ->manifest('/site.webmanifest');
```

`favicon()` is an alias of `icon()` and accepts the same `type`, `sizes`, and `media` arguments.

Route metadata uses the same names:

```php
use Laravel\Head\Enums\ImageType;
use Laravel\Head\Enums\Media;

Route::view('/dashboard', 'dashboard')->withHead(
    applicationName: 'Acme',
    colorScheme: 'light dark',
    appleWebAppTitle: 'Acme',
    webAppCapable: true,
    appleWebAppStatusBarStyle: 'black',
    favicon: [
        ['href' => '/favicon.svg', 'type' => ImageType::Svg],
        ['href' => '/favicon-32x32.png', 'type' => ImageType::Png, 'sizes' => '32x32'],
    ],
    appleTouchIcon: ['href' => '/apple-touch-icon.png', 'sizes' => '180x180'],
    appleTouchStartupImage: ['href' => '/launch.png', 'media' => Media::Portrait],
    manifest: '/site.webmanifest',
);
```

## Progressive Web Apps

The `pwa()` helper configures the common document `<head>` tags needed for an installable web app:

```php
Head::pwa(
    name: 'Acme',
    manifest: '/site.webmanifest',
    themeColor: '#0f172a',
    appleTouchIcon: '/apple-touch-icon.png',
    appleWebAppStatusBarStyle: 'black',
);
```

This renders the application name, web app manifest link, optional theme color, iOS standalone metadata, optional Apple status bar style, and optional Apple touch icon. The manifest JSON itself and service worker registration still belong to your application.

Use `pwa()` in defaults or runtime metadata. Route metadata supports the individual properties shown above.

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

For local assets, `preloadAsset()` and `prefetchAsset()` resolve the URL through the `asset()` helper and detect the `as` attribute from the file extension. Font preloads automatically include `crossorigin`, which the preload specification requires even for same-origin fonts:

```php
Head::preloadAsset('fonts/inter.woff2')
    ->prefetchAsset('images/next.webp');
```

```html
<link rel="preload" href="https://example.com/fonts/inter.woff2" as="font" crossorigin>
<link rel="prefetch" href="https://example.com/images/next.webp" as="image">
```

Pass `as` explicitly to override detection. `preloadAsset()` throws when the `as` attribute cannot be detected from the extension, since browsers ignore preloads without one; `prefetchAsset()` simply omits it.

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
use Laravel\Head\Enums\Media;

Head::meta('theme-color', '#ffffff', media: Media::Light)
    ->meta('theme-color', '#111827', media: Media::Dark);
```

`meta()` uses `name=` for regular meta tags. For keys that normally use `property=`, such as Open Graph (`og:`) or article metadata (`article:`), it switches automatically:

```php
Head::meta('description', 'About Acme')
    ->meta('og:title', 'About Acme');
```

```html
<meta name="description" content="About Acme">
<meta property="og:title" content="About Acme">
```

Pass `property: true` or `property: false` if you need to force one or the other.

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
                ->currency('USD')
                ->availability(OfferAvailability::InStock)
        )
);
```

The built-in factory methods are `article`, `blogPosting`, `product`, `offer`, `brand`, `breadcrumbs`, `faq`, `organization`, `person`, `webPage`, and `webSite`. Unknown factory methods fall back to a generic schema object so custom schema.org types can still be expressed.

Invalid JSON-LD schema data throws outside production and is logged as a warning in production.

### Breadcrumbs

Breadcrumb items may be added one at a time or in bulk. Positions are assigned automatically in the order the items are added:

```php
Head::schema(
    Schema::breadcrumbs()->items([
        'Home' => route('home'),
        'Shop' => route('shop.index'),
        'Shoes' => route('shop.category', 'shoes'),
    ])
);
```

Use `item()` to append a single crumb:

```php
Schema::breadcrumbs()
    ->item('Home', route('home'))
    ->item('Shop', route('shop.index'));
```

### FAQs

FAQ questions follow the same pattern. Add them one at a time with `question()` or in bulk with `questions()`:

```php
Head::schema(
    Schema::faq()->questions([
        'What is Laravel Head?' => 'A fluent API for managing the document head.',
        'Is it free?' => 'Yes, it is open source.',
    ])
);
```

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

Laravel Head resolves the page layers into tags for the current response. Where those tags are emitted depends on your stack.

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

Use the same `@head` directive in your Inertia root view:

```blade
<head>
    <meta charset="utf-8">
    @head

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.tsx'])
</head>
```

When Inertia is installed, Laravel Head automatically shares the page-managed head as an array of rendered element strings under a `head` prop on every page object:

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

Inertia renders these elements into the document head for you. Enable it with the `serverHead` option in your application's entry point:

```js
createInertiaApp({
    // ...
    serverHead: true,
})
```

> [!NOTE]
> When adding Laravel Head to an existing Inertia application, remove any title suffix callbacks from `resources/js/app.tsx` and `resources/js/ssr.tsx` so Head can manage the final document title.

The initial document is rendered by `@head`, hydrated on the client, and kept in sync on every Inertia visit. No client-side `<Head>` component or template is required. Page-managed tags receive stable `data-inertia` keys so Inertia can update or replace them during navigation.

The prop is not sent during partial reloads; the client keeps the head from the last full visit. If your application already uses the `head` prop for something else, change the prop name in a service provider:

```php
use Laravel\Head\Facades\Head;

public function boot(): void
{
    Head::inertia(prop: '_head');
}
```

Then point Inertia at the same prop with `serverHead: '_head'`.

#### Static Inertia Tags

Most tags should live in defaults, route metadata, or runtime metadata so Laravel Head can resolve the right value for each page. Inertia globals are only for document tags that should be rendered into the first HTML response and then left alone by Inertia for the rest of the session.

Register them in a service provider with `Head::inertiaGlobals()`:

```php
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadBuilder;

Head::inertiaGlobals(function (HeadBuilder $head) {
    $head
        ->viewport('width=device-width, initial-scale=1')
        ->colorScheme('light dark')
        ->icon('/favicon.svg', type: 'image/svg+xml')
        ->appleTouchIcon('/apple-touch-icon.png', sizes: '180x180')
        ->manifest('/site.webmanifest');
});
```

Inertia globals are excluded from the `head` prop, rendered without `data-inertia` ownership attributes, and never updated after the first response. They are a good fit for stable browser hints like viewport, color scheme, favicons, touch icons, and manifests. If a tag is page-specific, SEO-relevant, or might be overridden later, put it in `defaults`, route metadata, or runtime metadata instead.

Applications that want the resolved head as structured data (titles, Open Graph values, JSON-LD schemas, etc.) rather than rendered tags can still call `Head::toArray()`.

## Security Vulnerabilities

Please review [our security policy](https://github.com/laravel/head/security/policy) on how to report security vulnerabilities.

## License

Laravel Head is open-sourced software licensed under the [MIT license](LICENSE.md).
