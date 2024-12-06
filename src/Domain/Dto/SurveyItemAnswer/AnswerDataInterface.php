<?php

namespace App\Domain\Dto\SurveyItemAnswer;

use AndrewGos\ClassBuilder\Attribute as MA;
use App\Domain\Enum\SurveyItemTypeEnum;

#[MA\AvailableInheritors([
    ChoiceAnswerData::class,
    MultiChoiceAnswerData::class,
    CommentAnswerData::class,
])]
interface AnswerDataInterface
{
    public function getType(): SurveyItemTypeEnum;
}
