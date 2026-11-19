<?php

declare(strict_types=1);

namespace Laravel\Head\Schema;

use Laravel\Head\SchemaType;

#[SchemaType('FAQPage')]
class Faq extends SchemaObject
{
    /** @var array<int, array<string, mixed>> */
    protected array $questions = [];

    /**
     * @param  string|SchemaObject  $answer  The answer text, or an Answer schema object carrying its own properties.
     * @param  array<string, mixed>  $attributes  Additional Question properties, such as url, datePublished, or answerCount.
     */
    public function question(string $name, string|SchemaObject $answer, array $attributes = []): static
    {
        $this->questions[] = [
            '@type' => 'Question',
            'name' => $name,
            'acceptedAnswer' => $answer instanceof SchemaObject ? $answer : [
                '@type' => 'Answer',
                'text' => $answer,
            ],
            ...$attributes,
        ];

        return $this->set('mainEntity', $this->questions);
    }

    /** @param array<string, string|SchemaObject> $questions Questions keyed to their answers. */
    public function questions(array $questions): static
    {
        foreach ($questions as $name => $answer) {
            $this->question($name, $answer);
        }

        return $this;
    }
}
