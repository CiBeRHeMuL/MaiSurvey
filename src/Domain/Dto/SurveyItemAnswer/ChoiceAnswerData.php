<?php

namespace App\Domain\Dto\SurveyItemAnswer;

use AndrewGos\ClassBuilder\Attribute as MA;
use AndrewGos\ClassBuilder\Checker\FieldIsChecker;
use App\Domain\Dto\SurveyItem\Choice;
use App\Domain\Enum\SurveyItemTypeEnum;

#[MA\BuildIf(new FieldIsChecker('type', SurveyItemTypeEnum::Choice->value))]
readonly class ChoiceAnswerData implements AnswerDataInterface
{
    /**
     * @param SurveyItemTypeEnum $type
     * @param Choice $choice
     */
    public function __construct(
        public SurveyItemTypeEnum $type,
        public Choice $choice,
    ) {
    }

    public function getType(): SurveyItemTypeEnum
    {
        return $this->type;
    }

    public function getChoice(): Choice
    {
        return $this->choice;
    }
}
