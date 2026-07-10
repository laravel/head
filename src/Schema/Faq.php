<?php

declare(strict_types=1);

namespace Laravel\Head\Schema;

use Laravel\Head\SchemaType;

#[SchemaType('FAQPage')]
class Faq extends SchemaObject
{
    /** @var array<int, array{'@type': string, name: string, acceptedAnswer: array{'@type': string, text: string}}> */
    protected array $questions = [];

    public function question(string $name, string $answer): static
    {
        $this->questions[] = [
            '@type' => 'Question',
            'name' => $name,
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $answer,
            ],
        ];

        return $this->set('mainEntity', $this->questions);
    }

    /** @param array<string, string> $questions Questions keyed to their answers. */
    public function questions(array $questions): static
    {
        foreach ($questions as $name => $answer) {
            $this->question($name, $answer);
        }

        return $this;
    }
}
