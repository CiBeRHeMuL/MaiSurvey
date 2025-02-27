<?php

namespace App\Presentation\Web\Response\Model\SurveyStatItem;

use App\Domain\Enum\SurveyItemTypeEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use Symfony\Component\Serializer\Attribute\DiscriminatorMap;
use Symfony\Component\Serializer\Attribute\SerializedName;

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

    #[SerializedName('teacher_name')]
    public function getTeacherName(): string|null;

    #[SerializedName('teacher_id')]
    public function getTeacherId(): string|null;

    #[SerializedName('available_count')]
    public function getAvailableCount(): int;

    #[SerializedName('completed_count')]
    public function getCompletedCount(): int;
}
