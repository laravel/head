<?php

declare(strict_types=1);

namespace Laravel\Head\Schema;

use DateTimeInterface;
use Laravel\Head\SchemaType;

#[SchemaType('Article')]
class Article extends SchemaObject
{
    public function headline(string $headline): static
    {
        return $this->set('headline', $headline);
    }

    public function description(string $description): static
    {
        return $this->set('description', $description);
    }

    public function author(Person|Organization $author): static
    {
        return $this->set('author', $author);
    }

    public function publishedAt(DateTimeInterface|string $date): static
    {
        return $this->date('datePublished', $date);
    }

    public function modifiedAt(DateTimeInterface|string $date): static
    {
        return $this->date('dateModified', $date);
    }

    /**
     * @param  string|array<int, string>  $image
     */
    public function image(string|array $image): static
    {
        return $this->set('image', $image);
    }
}
