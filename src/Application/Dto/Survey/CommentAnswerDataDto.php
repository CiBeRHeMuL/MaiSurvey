<?php

namespace App\Application\Dto\Survey;

use App\Domain\Enum\SurveyItemTypeEnum;

readonly class CommentAnswerDataDto implements AnswerDataDtoInterface
{
    public function __construct(
        public string $comment,
    ) {
    }

    public function getType(): string
    {
        return SurveyItemTypeEnum::Comment->value;
    }
}
