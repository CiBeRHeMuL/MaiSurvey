<?php

namespace App\Domain\Dto\SurveyItemAnswer;

use AndrewGos\ClassBuilder\Attribute as MA;
use AndrewGos\ClassBuilder\Checker\FieldIsChecker;
use App\Domain\Enum\SurveyItemTypeEnum;

#[MA\BuildIf(new FieldIsChecker('type', SurveyItemTypeEnum::MultiChoice->value))]
readonly class MultiChoiceAnswerData implements AnswerDataInterface
{
    /**
     * @param SurveyItemTypeEnum $type
     * @param string[] $choices
     */
    public function __construct(
        public SurveyItemTypeEnum $type,
        public array $choices,
    ) {
    }

    public function getType(): SurveyItemTypeEnum
    {
        return $this->type;
    }

    /**
     * @return string[]
     */
    public function getChoices(): array
    {
        return $this->choices;
    }
}
