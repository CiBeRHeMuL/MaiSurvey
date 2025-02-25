<?php

namespace App\Presentation\Web\Response\Model\SurveyItemData;

use App\Domain\Dto\SurveyItem\Choice as DomainChoice;

readonly class Choice
{
    public function __construct(
        public string $text,
        public string $value,
    ) {
    }

    public static function fromChoice(DomainChoice $choice): self
    {
        return new self(
            $choice->text,
            $choice->value,
        );
    }
}
