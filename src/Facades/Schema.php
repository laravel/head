<?php

declare(strict_types=1);

namespace Laravel\Head\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Laravel\Head\Schema\Article article()
 * @method static \Laravel\Head\Schema\BlogPosting blogPosting()
 * @method static \Laravel\Head\Schema\Product product()
 * @method static \Laravel\Head\Schema\Offer offer()
 * @method static \Laravel\Head\Schema\Breadcrumbs breadcrumbs()
 * @method static \Laravel\Head\Schema\Faq faq()
 * @method static \Laravel\Head\Schema\Organization organization()
 * @method static \Laravel\Head\Schema\Person person()
 * @method static \Laravel\Head\Schema\WebPage webPage()
 * @method static \Laravel\Head\Schema\WebSite webSite()
 * @method static \Laravel\Head\Schema\SchemaObject make(string $type)
 * @method static \Laravel\Head\Schema register(string $class)
 *
 * @see \Laravel\Head\Schema
 */
class Schema extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'head.schema';
    }
}
