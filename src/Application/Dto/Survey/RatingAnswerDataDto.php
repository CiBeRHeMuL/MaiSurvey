<?php

namespace App\Application\Dto\Survey;

use App\Domain\Enum\SurveyItemTypeEnum;

readonly class RatingAnswerDataDto implements AnswerDataDtoInterface
{
    public function __construct(
        public int $rating,
    ) {
    }

    public function getType(): string
    {
        return SurveyItemTypeEnum::Rating->value;
    }
}
