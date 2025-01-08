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
     * @param int[] $ratings
     */
    public function __construct(
        public SurveyItemTypeEnum $type,
        #[MA\ArrayType('integer')]
        public array $ratings,
    ) {
    }

    public function getType(): SurveyItemTypeEnum
    {
        return $this->type;
    }

    public function getRatings(): array
    {
        return $this->ratings;
    }
}
