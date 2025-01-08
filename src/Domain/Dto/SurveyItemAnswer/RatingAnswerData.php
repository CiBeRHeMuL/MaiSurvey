<?php

namespace App\Domain\Dto\SurveyItemAnswer;

use AndrewGos\ClassBuilder\Attribute as MA;
use AndrewGos\ClassBuilder\Checker\FieldIsChecker;
use App\Domain\Enum\SurveyItemTypeEnum;

#[MA\BuildIf(new FieldIsChecker('type', SurveyItemTypeEnum::Rating->value))]
readonly class RatingAnswerData implements AnswerDataInterface
{
    public function __construct(
        public SurveyItemTypeEnum $type,
        public int $rating,
    ) {
    }

    public function getType(): SurveyItemTypeEnum
    {
        return SurveyItemTypeEnum::Rating;
    }

    public function getRating(): int
    {
        return $this->rating;
    }
}
