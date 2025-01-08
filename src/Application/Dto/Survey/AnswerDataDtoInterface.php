<?php

namespace App\Application\Dto\Survey;

use App\Application\OpenApi\Attribute as LOA;
use App\Domain\Enum\SurveyItemTypeEnum;
use Symfony\Component\Serializer\Attribute\DiscriminatorMap;

#[DiscriminatorMap(
    typeProperty: 'type',
    mapping: [
        SurveyItemTypeEnum::Comment->value => CommentAnswerDataDto::class,
        SurveyItemTypeEnum::Choice->value => ChoiceAnswerDataDto::class,
        SurveyItemTypeEnum::MultiChoice->value => MultiChoiceAnswerDataDto::class,
        SurveyItemTypeEnum::Rating->value => RatingAnswerDataDto::class,
    ],
)]
interface AnswerDataDtoInterface
{
    #[LOA\Enum(SurveyItemTypeEnum::class)]
    public function getType(): string;
}
