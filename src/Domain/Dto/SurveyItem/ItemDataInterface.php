<?php

namespace App\Domain\Dto\SurveyItem;

use AndrewGos\ClassBuilder\Attribute as MA;
use App\Domain\Enum\SurveyItemTypeEnum;

#[MA\AvailableInheritors([
    ChoiceItemData::class,
    MultiChoiceItemData::class,
    CommentItemData::class,
    RatingItemData::class,
])]
interface ItemDataInterface
{
    public function getType(): SurveyItemTypeEnum;
}
