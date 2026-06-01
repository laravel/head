<?php

declare(strict_types=1);

namespace Laravel\Head;

use Illuminate\Contracts\Foundation\Application;
use Laravel\Head\Exceptions\InvalidSchema;
use Psr\Log\LoggerInterface;

class SchemaValidator
{
    public function __construct(protected Application $app) {}

    /**
     * @param  array<string, mixed>  $schema
     */
    public function validate(array $schema): void
    {
        $message = match (true) {
            ($schema['@context'] ?? null) !== 'https://schema.org' => 'JSON-LD schemas must use the https://schema.org context.',
            ! isset($schema['@type']) || $schema['@type'] === '' => 'JSON-LD schemas must define an @type.',
            $this->containsEmptyValue($schema) => 'JSON-LD schemas may not contain null or empty string values.',
            default => null,
        };

        if (is_null($message)) {
            return;
        }

        if ($this->app->environment('production')) {
            if ($this->app->bound(LoggerInterface::class)) {
                $this->app->make(LoggerInterface::class)->warning($message, ['schema' => $schema]);
            }

            return;
        }

        throw new InvalidSchema($message);
    }

    /**
     * @param  array<mixed>  $values
     */
    protected function containsEmptyValue(array $values): bool
    {
        foreach ($values as $value) {
            if ($value === null || $value === '') {
                return true;
            }

            if (is_array($value) && $this->containsEmptyValue($value)) {
                return true;
            }
        }

        return false;
    }
}
