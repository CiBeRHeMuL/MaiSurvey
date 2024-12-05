<?php

namespace App\Domain\Dto\SurveyItemAnswer;

use AndrewGos\ClassBuilder\Attribute as MA;
use App\Domain\Enum\SurveyItemTypeEnum;

#[MA\AvailableInheritors()]
interface AnswerDataInterface
{
    public function getType(): SurveyItemTypeEnum;
}
