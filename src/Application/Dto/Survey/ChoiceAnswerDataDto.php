<?php

namespace App\Application\Dto\Survey;

use App\Domain\Enum\SurveyItemTypeEnum;

readonly class ChoiceAnswerDataDto implements AnswerDataDtoInterface
{
    public function __construct(
        public string $choice,
    ) {
    }

    public function getType(): string
    {
        return SurveyItemTypeEnum::Choice->value;
    }
}
