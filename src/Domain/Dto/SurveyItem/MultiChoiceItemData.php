<?php

namespace App\Domain\Dto\SurveyItem;

use AndrewGos\ClassBuilder\Attribute as MA;
use AndrewGos\ClassBuilder\Checker\FieldIsChecker;
use App\Domain\Enum\SurveyItemTypeEnum;

#[MA\BuildIf(new FieldIsChecker('type', SurveyItemTypeEnum::MultiChoice->value))]
readonly class MultiChoiceItemData implements ItemDataInterface
{
    /**
     * @param SurveyItemTypeEnum $type
     * @param Choice[] $choices
     */
    public function __construct(
        public SurveyItemTypeEnum $type,
        #[MA\ArrayType(Choice::class)]
        public array $choices,
    ) {
    }

    public function getType(): SurveyItemTypeEnum
    {
        return $this->type;
    }

    public function getChoices(): array
    {
        return $this->choices;
    }
}
