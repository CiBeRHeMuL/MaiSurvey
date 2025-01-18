<?php

namespace App\Application\Mapper\Survey;

use App\Application\Dto\Survey\Complete as Root;
use App\Domain\Dto\SurveyItemAnswer as Dest;
use App\Domain\Enum\SurveyItemTypeEnum;

class AnswerDataMapper
{
    public static function map(Root\AnswerDataDtoInterface $dto): Dest\AnswerDataInterface
    {
        return match ($dto::class) {
            Root\CommentAnswerDataDto::class => new Dest\CommentAnswerData(SurveyItemTypeEnum::from($dto->getType()), $dto->comment),
            Root\ChoiceAnswerDataDto::class => new Dest\ChoiceAnswerData(SurveyItemTypeEnum::from($dto->getType()), $dto->choice),
            Root\MultiChoiceAnswerDataDto::class => new Dest\MultiChoiceAnswerData(SurveyItemTypeEnum::from($dto->getType()), $dto->choices),
            Root\RatingAnswerDataDto::class => new Dest\RatingAnswerData(SurveyItemTypeEnum::from($dto->getType()), $dto->rating),
        };
    }
}
