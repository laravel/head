<?php

/**
 * Editor helper declarations for the withHead() route macros registered by
 * laravel/head. This file is never autoloaded or executed; editors such as
 * VS Code with Intelephense index it so withHead() autocompletes on Laravel's
 * routing classes. Keep the signatures in sync with the macros registered
 * by \Laravel\Head\Routing\HeadRouteMacros.
 *
 * @see \Laravel\Head\Routing\HeadRouteMacros
 */

namespace Illuminate\Routing {
    use Laravel\Head\Enums\RobotsRule;

    class Router
    {
        /**
         * Define head metadata for the route, stored through Laravel's native
         * route metadata API. Provided by laravel/head.
         *
         * @param  array<mixed, mixed>|string|null  $title
         * @param  array<mixed, mixed>|string|true|null  $canonical
         * @param  array<int, string|RobotsRule>|string|RobotsRule|null  $robots
         * @param  array<string, mixed>|null  $og
         * @param  array<mixed, mixed>|string|null  $ogImage
         * @param  array<mixed, mixed>|string|null  $ogVideo
         * @param  array<mixed, mixed>|string|null  $ogAudio
         * @param  array<string, mixed>|null  $twitter
         * @param  array<mixed, mixed>|string|null  $twitterImage
         * @param  array<mixed, mixed>|string|null  $preload
         * @param  array<mixed, mixed>|string|null  $prefetch
         * @param  array<mixed, mixed>|string|null  $preconnect
         * @param  array<mixed, mixed>|string|null  $dnsPrefetch
         * @param  array<string, string>|null  $alternates
         * @param  array<mixed, mixed>|string|null  $feed
         * @param  array<mixed, mixed>|string|null  $icon
         * @param  array<mixed, mixed>|string|null  $favicon
         * @param  array<mixed, mixed>|string|null  $appleTouchIcon
         * @param  array<mixed, mixed>|string|null  $appleTouchStartupImage
         * @param  array<mixed, mixed>|string|null  $maskIcon
         * @param  array<mixed, mixed>|string|null  $manifest
         * @param  array<mixed, mixed>|null  $schema
         * @param  array<mixed, mixed>|null  $meta
         * @param  array<mixed, mixed>|null  $link
         * @param  array<string, mixed>  $extensions
         */
        public function withHead(
            string|array|null $title = null,
            ?string $description = null,
            string|array|true|null $canonical = null,
            string|RobotsRule|array|null $robots = null,
            ?string $themeColor = null,
            ?string $applicationName = null,
            ?string $colorScheme = null,
            ?string $referrer = null,
            ?string $viewport = null,
            ?string $appleWebAppTitle = null,
            ?bool $webAppCapable = null,
            ?string $appleWebAppStatusBarStyle = null,
            ?array $og = null,
            string|array|null $ogImage = null,
            string|array|null $ogVideo = null,
            string|array|null $ogAudio = null,
            ?array $twitter = null,
            string|array|null $twitterImage = null,
            string|array|null $preload = null,
            string|array|null $prefetch = null,
            string|array|null $preconnect = null,
            string|array|null $dnsPrefetch = null,
            ?array $alternates = null,
            string|array|null $feed = null,
            string|array|null $icon = null,
            string|array|null $favicon = null,
            string|array|null $appleTouchIcon = null,
            string|array|null $appleTouchStartupImage = null,
            string|array|null $maskIcon = null,
            string|array|null $manifest = null,
            ?array $schema = null,
            ?array $meta = null,
            ?array $link = null,
            array $extensions = [],
        ): RouteRegistrar {}
    }
}

namespace Illuminate\Routing {
    use Laravel\Head\Enums\RobotsRule;

    class RouteRegistrar
    {
        /**
         * Define head metadata for the route, stored through Laravel's native
         * route metadata API. Provided by laravel/head.
         *
         * @param  array<mixed, mixed>|string|null  $title
         * @param  array<mixed, mixed>|string|true|null  $canonical
         * @param  array<int, string|RobotsRule>|string|RobotsRule|null  $robots
         * @param  array<string, mixed>|null  $og
         * @param  array<mixed, mixed>|string|null  $ogImage
         * @param  array<mixed, mixed>|string|null  $ogVideo
         * @param  array<mixed, mixed>|string|null  $ogAudio
         * @param  array<string, mixed>|null  $twitter
         * @param  array<mixed, mixed>|string|null  $twitterImage
         * @param  array<mixed, mixed>|string|null  $preload
         * @param  array<mixed, mixed>|string|null  $prefetch
         * @param  array<mixed, mixed>|string|null  $preconnect
         * @param  array<mixed, mixed>|string|null  $dnsPrefetch
         * @param  array<string, string>|null  $alternates
         * @param  array<mixed, mixed>|string|null  $feed
         * @param  array<mixed, mixed>|string|null  $icon
         * @param  array<mixed, mixed>|string|null  $favicon
         * @param  array<mixed, mixed>|string|null  $appleTouchIcon
         * @param  array<mixed, mixed>|string|null  $appleTouchStartupImage
         * @param  array<mixed, mixed>|string|null  $maskIcon
         * @param  array<mixed, mixed>|string|null  $manifest
         * @param  array<mixed, mixed>|null  $schema
         * @param  array<mixed, mixed>|null  $meta
         * @param  array<mixed, mixed>|null  $link
         * @param  array<string, mixed>  $extensions
         */
        public function withHead(
            string|array|null $title = null,
            ?string $description = null,
            string|array|true|null $canonical = null,
            string|RobotsRule|array|null $robots = null,
            ?string $themeColor = null,
            ?string $applicationName = null,
            ?string $colorScheme = null,
            ?string $referrer = null,
            ?string $viewport = null,
            ?string $appleWebAppTitle = null,
            ?bool $webAppCapable = null,
            ?string $appleWebAppStatusBarStyle = null,
            ?array $og = null,
            string|array|null $ogImage = null,
            string|array|null $ogVideo = null,
            string|array|null $ogAudio = null,
            ?array $twitter = null,
            string|array|null $twitterImage = null,
            string|array|null $preload = null,
            string|array|null $prefetch = null,
            string|array|null $preconnect = null,
            string|array|null $dnsPrefetch = null,
            ?array $alternates = null,
            string|array|null $feed = null,
            string|array|null $icon = null,
            string|array|null $favicon = null,
            string|array|null $appleTouchIcon = null,
            string|array|null $appleTouchStartupImage = null,
            string|array|null $maskIcon = null,
            string|array|null $manifest = null,
            ?array $schema = null,
            ?array $meta = null,
            ?array $link = null,
            array $extensions = [],
        ): RouteRegistrar {}
    }
}

namespace Illuminate\Routing {
    use Laravel\Head\Enums\RobotsRule;

    class Route
    {
        /**
         * Define head metadata for the route, stored through Laravel's native
         * route metadata API. Provided by laravel/head.
         *
         * @param  array<mixed, mixed>|string|null  $title
         * @param  array<mixed, mixed>|string|true|null  $canonical
         * @param  array<int, string|RobotsRule>|string|RobotsRule|null  $robots
         * @param  array<string, mixed>|null  $og
         * @param  array<mixed, mixed>|string|null  $ogImage
         * @param  array<mixed, mixed>|string|null  $ogVideo
         * @param  array<mixed, mixed>|string|null  $ogAudio
         * @param  array<string, mixed>|null  $twitter
         * @param  array<mixed, mixed>|string|null  $twitterImage
         * @param  array<mixed, mixed>|string|null  $preload
         * @param  array<mixed, mixed>|string|null  $prefetch
         * @param  array<mixed, mixed>|string|null  $preconnect
         * @param  array<mixed, mixed>|string|null  $dnsPrefetch
         * @param  array<string, string>|null  $alternates
         * @param  array<mixed, mixed>|string|null  $feed
         * @param  array<mixed, mixed>|string|null  $icon
         * @param  array<mixed, mixed>|string|null  $favicon
         * @param  array<mixed, mixed>|string|null  $appleTouchIcon
         * @param  array<mixed, mixed>|string|null  $appleTouchStartupImage
         * @param  array<mixed, mixed>|string|null  $maskIcon
         * @param  array<mixed, mixed>|string|null  $manifest
         * @param  array<mixed, mixed>|null  $schema
         * @param  array<mixed, mixed>|null  $meta
         * @param  array<mixed, mixed>|null  $link
         * @param  array<string, mixed>  $extensions
         */
        public function withHead(
            string|array|null $title = null,
            ?string $description = null,
            string|array|true|null $canonical = null,
            string|RobotsRule|array|null $robots = null,
            ?string $themeColor = null,
            ?string $applicationName = null,
            ?string $colorScheme = null,
            ?string $referrer = null,
            ?string $viewport = null,
            ?string $appleWebAppTitle = null,
            ?bool $webAppCapable = null,
            ?string $appleWebAppStatusBarStyle = null,
            ?array $og = null,
            string|array|null $ogImage = null,
            string|array|null $ogVideo = null,
            string|array|null $ogAudio = null,
            ?array $twitter = null,
            string|array|null $twitterImage = null,
            string|array|null $preload = null,
            string|array|null $prefetch = null,
            string|array|null $preconnect = null,
            string|array|null $dnsPrefetch = null,
            ?array $alternates = null,
            string|array|null $feed = null,
            string|array|null $icon = null,
            string|array|null $favicon = null,
            string|array|null $appleTouchIcon = null,
            string|array|null $appleTouchStartupImage = null,
            string|array|null $maskIcon = null,
            string|array|null $manifest = null,
            ?array $schema = null,
            ?array $meta = null,
            ?array $link = null,
            array $extensions = [],
        ): Route {}
    }
}

namespace Illuminate\Routing {
    use Laravel\Head\Enums\RobotsRule;

    class PendingResourceRegistration
    {
        /**
         * Define head metadata for the route, stored through Laravel's native
         * route metadata API. Provided by laravel/head.
         *
         * @param  array<mixed, mixed>|string|null  $title
         * @param  array<mixed, mixed>|string|true|null  $canonical
         * @param  array<int, string|RobotsRule>|string|RobotsRule|null  $robots
         * @param  array<string, mixed>|null  $og
         * @param  array<mixed, mixed>|string|null  $ogImage
         * @param  array<mixed, mixed>|string|null  $ogVideo
         * @param  array<mixed, mixed>|string|null  $ogAudio
         * @param  array<string, mixed>|null  $twitter
         * @param  array<mixed, mixed>|string|null  $twitterImage
         * @param  array<mixed, mixed>|string|null  $preload
         * @param  array<mixed, mixed>|string|null  $prefetch
         * @param  array<mixed, mixed>|string|null  $preconnect
         * @param  array<mixed, mixed>|string|null  $dnsPrefetch
         * @param  array<string, string>|null  $alternates
         * @param  array<mixed, mixed>|string|null  $feed
         * @param  array<mixed, mixed>|string|null  $icon
         * @param  array<mixed, mixed>|string|null  $favicon
         * @param  array<mixed, mixed>|string|null  $appleTouchIcon
         * @param  array<mixed, mixed>|string|null  $appleTouchStartupImage
         * @param  array<mixed, mixed>|string|null  $maskIcon
         * @param  array<mixed, mixed>|string|null  $manifest
         * @param  array<mixed, mixed>|null  $schema
         * @param  array<mixed, mixed>|null  $meta
         * @param  array<mixed, mixed>|null  $link
         * @param  array<string, mixed>  $extensions
         */
        public function withHead(
            string|array|null $title = null,
            ?string $description = null,
            string|array|true|null $canonical = null,
            string|RobotsRule|array|null $robots = null,
            ?string $themeColor = null,
            ?string $applicationName = null,
            ?string $colorScheme = null,
            ?string $referrer = null,
            ?string $viewport = null,
            ?string $appleWebAppTitle = null,
            ?bool $webAppCapable = null,
            ?string $appleWebAppStatusBarStyle = null,
            ?array $og = null,
            string|array|null $ogImage = null,
            string|array|null $ogVideo = null,
            string|array|null $ogAudio = null,
            ?array $twitter = null,
            string|array|null $twitterImage = null,
            string|array|null $preload = null,
            string|array|null $prefetch = null,
            string|array|null $preconnect = null,
            string|array|null $dnsPrefetch = null,
            ?array $alternates = null,
            string|array|null $feed = null,
            string|array|null $icon = null,
            string|array|null $favicon = null,
            string|array|null $appleTouchIcon = null,
            string|array|null $appleTouchStartupImage = null,
            string|array|null $maskIcon = null,
            string|array|null $manifest = null,
            ?array $schema = null,
            ?array $meta = null,
            ?array $link = null,
            array $extensions = [],
        ): PendingResourceRegistration {}
    }
}

namespace Illuminate\Routing {
    use Laravel\Head\Enums\RobotsRule;

    class PendingSingletonResourceRegistration
    {
        /**
         * Define head metadata for the route, stored through Laravel's native
         * route metadata API. Provided by laravel/head.
         *
         * @param  array<mixed, mixed>|string|null  $title
         * @param  array<mixed, mixed>|string|true|null  $canonical
         * @param  array<int, string|RobotsRule>|string|RobotsRule|null  $robots
         * @param  array<string, mixed>|null  $og
         * @param  array<mixed, mixed>|string|null  $ogImage
         * @param  array<mixed, mixed>|string|null  $ogVideo
         * @param  array<mixed, mixed>|string|null  $ogAudio
         * @param  array<string, mixed>|null  $twitter
         * @param  array<mixed, mixed>|string|null  $twitterImage
         * @param  array<mixed, mixed>|string|null  $preload
         * @param  array<mixed, mixed>|string|null  $prefetch
         * @param  array<mixed, mixed>|string|null  $preconnect
         * @param  array<mixed, mixed>|string|null  $dnsPrefetch
         * @param  array<string, string>|null  $alternates
         * @param  array<mixed, mixed>|string|null  $feed
         * @param  array<mixed, mixed>|string|null  $icon
         * @param  array<mixed, mixed>|string|null  $favicon
         * @param  array<mixed, mixed>|string|null  $appleTouchIcon
         * @param  array<mixed, mixed>|string|null  $appleTouchStartupImage
         * @param  array<mixed, mixed>|string|null  $maskIcon
         * @param  array<mixed, mixed>|string|null  $manifest
         * @param  array<mixed, mixed>|null  $schema
         * @param  array<mixed, mixed>|null  $meta
         * @param  array<mixed, mixed>|null  $link
         * @param  array<string, mixed>  $extensions
         */
        public function withHead(
            string|array|null $title = null,
            ?string $description = null,
            string|array|true|null $canonical = null,
            string|RobotsRule|array|null $robots = null,
            ?string $themeColor = null,
            ?string $applicationName = null,
            ?string $colorScheme = null,
            ?string $referrer = null,
            ?string $viewport = null,
            ?string $appleWebAppTitle = null,
            ?bool $webAppCapable = null,
            ?string $appleWebAppStatusBarStyle = null,
            ?array $og = null,
            string|array|null $ogImage = null,
            string|array|null $ogVideo = null,
            string|array|null $ogAudio = null,
            ?array $twitter = null,
            string|array|null $twitterImage = null,
            string|array|null $preload = null,
            string|array|null $prefetch = null,
            string|array|null $preconnect = null,
            string|array|null $dnsPrefetch = null,
            ?array $alternates = null,
            string|array|null $feed = null,
            string|array|null $icon = null,
            string|array|null $favicon = null,
            string|array|null $appleTouchIcon = null,
            string|array|null $appleTouchStartupImage = null,
            string|array|null $maskIcon = null,
            string|array|null $manifest = null,
            ?array $schema = null,
            ?array $meta = null,
            ?array $link = null,
            array $extensions = [],
        ): PendingSingletonResourceRegistration {}
    }
}

namespace Illuminate\Support\Facades {
    use Illuminate\Routing\RouteRegistrar;
    use Laravel\Head\Enums\RobotsRule;

    class Route
    {
        /**
         * Define head metadata for the route, stored through Laravel's native
         * route metadata API. Provided by laravel/head.
         *
         * @param  array<mixed, mixed>|string|null  $title
         * @param  array<mixed, mixed>|string|true|null  $canonical
         * @param  array<int, string|RobotsRule>|string|RobotsRule|null  $robots
         * @param  array<string, mixed>|null  $og
         * @param  array<mixed, mixed>|string|null  $ogImage
         * @param  array<mixed, mixed>|string|null  $ogVideo
         * @param  array<mixed, mixed>|string|null  $ogAudio
         * @param  array<string, mixed>|null  $twitter
         * @param  array<mixed, mixed>|string|null  $twitterImage
         * @param  array<mixed, mixed>|string|null  $preload
         * @param  array<mixed, mixed>|string|null  $prefetch
         * @param  array<mixed, mixed>|string|null  $preconnect
         * @param  array<mixed, mixed>|string|null  $dnsPrefetch
         * @param  array<string, string>|null  $alternates
         * @param  array<mixed, mixed>|string|null  $feed
         * @param  array<mixed, mixed>|string|null  $icon
         * @param  array<mixed, mixed>|string|null  $favicon
         * @param  array<mixed, mixed>|string|null  $appleTouchIcon
         * @param  array<mixed, mixed>|string|null  $appleTouchStartupImage
         * @param  array<mixed, mixed>|string|null  $maskIcon
         * @param  array<mixed, mixed>|string|null  $manifest
         * @param  array<mixed, mixed>|null  $schema
         * @param  array<mixed, mixed>|null  $meta
         * @param  array<mixed, mixed>|null  $link
         * @param  array<string, mixed>  $extensions
         */
        public static function withHead(
            string|array|null $title = null,
            ?string $description = null,
            string|array|true|null $canonical = null,
            string|RobotsRule|array|null $robots = null,
            ?string $themeColor = null,
            ?string $applicationName = null,
            ?string $colorScheme = null,
            ?string $referrer = null,
            ?string $viewport = null,
            ?string $appleWebAppTitle = null,
            ?bool $webAppCapable = null,
            ?string $appleWebAppStatusBarStyle = null,
            ?array $og = null,
            string|array|null $ogImage = null,
            string|array|null $ogVideo = null,
            string|array|null $ogAudio = null,
            ?array $twitter = null,
            string|array|null $twitterImage = null,
            string|array|null $preload = null,
            string|array|null $prefetch = null,
            string|array|null $preconnect = null,
            string|array|null $dnsPrefetch = null,
            ?array $alternates = null,
            string|array|null $feed = null,
            string|array|null $icon = null,
            string|array|null $favicon = null,
            string|array|null $appleTouchIcon = null,
            string|array|null $appleTouchStartupImage = null,
            string|array|null $maskIcon = null,
            string|array|null $manifest = null,
            ?array $schema = null,
            ?array $meta = null,
            ?array $link = null,
            array $extensions = [],
        ): RouteRegistrar {}
    }
}
