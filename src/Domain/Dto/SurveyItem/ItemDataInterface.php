<?php

namespace App\Domain\Dto\SurveyItem;

use AndrewGos\ClassBuilder\Attribute as MA;
use App\Domain\Enum\SurveyItemTypeEnum;

#[MA\AvailableInheritors()]
interface ItemDataInterface
{
    public function getType(): SurveyItemTypeEnum;
}
