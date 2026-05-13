<?php

declare(strict_types=1);

namespace Laravel\Head\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Laravel\Head\Schema\SchemaObject article()
 * @method static \Laravel\Head\Schema\SchemaObject blogPosting()
 * @method static \Laravel\Head\Schema\SchemaObject product()
 * @method static \Laravel\Head\Schema\SchemaObject offer()
 * @method static \Laravel\Head\Schema\SchemaObject breadcrumbs()
 * @method static \Laravel\Head\Schema\SchemaObject faq()
 * @method static \Laravel\Head\Schema\SchemaObject organization()
 * @method static \Laravel\Head\Schema\SchemaObject person()
 * @method static \Laravel\Head\Schema\SchemaObject webPage()
 * @method static \Laravel\Head\Schema\SchemaObject webSite()
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
