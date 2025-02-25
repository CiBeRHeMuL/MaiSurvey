<?php

namespace App\Domain\Dto\SurveyItem;

readonly class Choice
{
    public function __construct(
        public string $text,
        public string $value,
    ) {
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
