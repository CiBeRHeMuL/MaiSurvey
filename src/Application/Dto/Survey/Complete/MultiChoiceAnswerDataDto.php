<?php

namespace App\Application\Dto\Survey\Complete;

use App\Domain\Enum\SurveyItemTypeEnum;

readonly class MultiChoiceAnswerDataDto implements AnswerDataDtoInterface
{
    /**
     * @param string[] $choices
     */
    public function __construct(
        public array $choices,
    ) {
    }

    public function getType(): string
    {
        return SurveyItemTypeEnum::MultiChoice->value;
    }
}
