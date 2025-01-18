<?php

namespace App\Application\Dto\Survey\Create;

use App\Application\OpenApi\Attribute as LOA;
use App\Domain\Enum\SurveyItemTypeEnum;
use Symfony\Component\Serializer\Attribute\DiscriminatorMap;

#[DiscriminatorMap(
    typeProperty: 'type',
    mapping: [
        SurveyItemTypeEnum::Comment->value => CommentItemData::class,
        SurveyItemTypeEnum::Choice->value => ChoiceItemData::class,
        SurveyItemTypeEnum::MultiChoice->value => MultiChoiceItemData::class,
        SurveyItemTypeEnum::Rating->value => RatingItemData::class,
    ],
)]
interface ItemDataInterface
{
    #[LOA\Enum(SurveyItemTypeEnum::class)]
    public function getType(): string;
}
