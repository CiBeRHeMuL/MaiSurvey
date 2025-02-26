<?php

namespace App\Presentation\Web\Response\Model\SurveyStatItem;

use App\Domain\Enum\SurveyItemTypeEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use Symfony\Component\Serializer\Attribute\DiscriminatorMap;

#[DiscriminatorMap(
    typeProperty: 'type',
    mapping: [
        SurveyItemTypeEnum::Comment->value => CommentStatData::class,
        SurveyItemTypeEnum::Choice->value => ChoiceStatData::class,
        SurveyItemTypeEnum::MultiChoice->value => MultiChoiceStatData::class,
        SurveyItemTypeEnum::Rating->value => RatingStatData::class,
    ],
)]
interface StatDataInterface
{
    #[LOA\Enum(SurveyItemTypeEnum::class)]
    public function getType(): string;

    public function getTeacherName(): string|null;

    public function getTeacherId(): string|null;

    public function getAvailableCount(): int;

    public function getCompletedCount(): int;
}
