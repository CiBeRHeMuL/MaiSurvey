<?php

namespace App\Domain\Dto\SurveyItem;

use AndrewGos\ClassBuilder\Attribute as MA;
use AndrewGos\ClassBuilder\Checker\FieldIsChecker;
use App\Domain\Enum\SurveyItemTypeEnum;

#[MA\BuildIf(new FieldIsChecker('type', SurveyItemTypeEnum::Rating->value))]
readonly class RatingItemData implements ItemDataInterface
{
    /**
     * @param SurveyItemTypeEnum $type
     * @param int $min
     * @param int $max
     */
    public function __construct(
        public SurveyItemTypeEnum $type,
        public int $min,
        public int $max,
    ) {
    }

    public function getType(): SurveyItemTypeEnum
    {
        return $this->type;
    }

    public function getMin(): int
    {
        return $this->min;
    }

    public function getMax(): int
    {
        return $this->max;
    }
}
