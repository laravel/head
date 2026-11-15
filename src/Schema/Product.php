<?php

declare(strict_types=1);

namespace Laravel\Head\Schema;

use InvalidArgumentException;
use Laravel\Head\SchemaType;

#[SchemaType('Product')]
class Product extends SchemaObject
{
    public function name(string $name): static
    {
        return $this->set('name', $name);
    }

    public function description(string $description): static
    {
        return $this->set('description', $description);
    }

    /**
     * @param  string|array<int, string>  $image
     */
    public function image(string|array $image): static
    {
        return $this->set('image', $image);
    }

    public function sku(string $sku): static
    {
        return $this->set('sku', $sku);
    }

    public function brand(Brand|Organization $brand): static
    {
        return $this->set('brand', $brand);
    }

    /**
     * @param  Offer|array<int, Offer>  $offers
     */
    public function offers(Offer|array $offers): static
    {
        return $this->set('offers', $offers);
    }

    public function gtin(string $gtin): static
    {
        $cleanGtin = preg_replace('/\D/', '', $gtin) ?? '';

        if (! in_array(strlen($cleanGtin), [8, 12, 13, 14], true)) {
            throw new InvalidArgumentException(
                "Invalid GTIN [{$gtin}]. GTIN must contain exactly 8, 12, 13, or 14 digits.",
            );
        }

        return $this->set('gtin', $cleanGtin);
    }

    public function gtin8(string $gtin): static
    {
        return $this->setGtinLength('gtin8', $gtin, 8);
    }

    public function gtin12(string $gtin): static
    {
        return $this->setGtinLength('gtin12', $gtin, 12);
    }

    public function gtin13(string $gtin): static
    {
        return $this->setGtinLength('gtin13', $gtin, 13);
    }

    public function gtin14(string $gtin): static
    {
        return $this->setGtinLength('gtin14', $gtin, 14);
    }

    public function mpn(string $mpn): static
    {
        return $this->set('mpn', $mpn);
    }

    public function category(string $category): static
    {
        return $this->set('category', $category);
    }

    protected function setGtinLength(string $field, string $gtin, int $length): static
    {
        $cleanGtin = preg_replace('/\D/', '', $gtin) ?? '';

        if (strlen($cleanGtin) !== $length) {
            throw new InvalidArgumentException(
                "Invalid [{$field}] [{$gtin}]. Must contain exactly {$length} digits.",
            );
        }

        return $this->set($field, $cleanGtin);
    }
}
