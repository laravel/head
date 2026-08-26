<?php

declare(strict_types=1);

namespace Laravel\Head\Facades;

use Illuminate\Support\Facades\Facade;
use Laravel\Head\Schema\SchemaFactory;

/**
 * @method static \Laravel\Head\Schema\Article article()
 * @method static \Laravel\Head\Schema\BlogPosting blogPosting()
 * @method static \Laravel\Head\Schema\Event event()
 * @method static \Laravel\Head\Schema\Product product()
 * @method static \Laravel\Head\Schema\Offer offer()
 * @method static \Laravel\Head\Schema\Brand brand()
 * @method static \Laravel\Head\Schema\Breadcrumbs breadcrumbs()
 * @method static \Laravel\Head\Schema\Faq faq()
 * @method static \Laravel\Head\Schema\Organization organization()
 * @method static \Laravel\Head\Schema\Person person()
 * @method static \Laravel\Head\Schema\WebPage webPage()
 * @method static \Laravel\Head\Schema\WebSite webSite()
 * @method static ($type is class-string<TSchema> ? TSchema : \Laravel\Head\Schema\SchemaObject) make<TSchema of \Laravel\Head\Schema\SchemaObject>(class-string<TSchema>|string $type)
 * @method static \Laravel\Head\Schema\SchemaFactory register(string $class)
 *
 * @see SchemaFactory
 */
class Schema extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'head.schema';
    }
}
