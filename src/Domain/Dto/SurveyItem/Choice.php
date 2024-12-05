<?php

namespace App\Domain\Dto\SurveyItem;

readonly class Choice
{
    public function __construct(
        public string $text,
        public string $value,
    ) {
    }
}
