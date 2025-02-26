<?php

namespace App\Domain\Dto\SurveyStatItem;

use AndrewGos\ClassBuilder\Attribute as MA;
use App\Domain\Enum\SurveyItemTypeEnum;
use Symfony\Component\Uid\Uuid;

#[MA\AvailableInheritors([
    ChoiceStatData::class,
    MultiChoiceStatData::class,
    CommentStatData::class,
    RatingStatData::class,
])]
interface StatDataInterface
{
    public function getType(): SurveyItemTypeEnum;

    public function getTeacherName(): string|null;

    public function getTeacherId(): Uuid|null;

    public function getAvailableCount(): int;

    public function getCompletedCount(): int;
}
