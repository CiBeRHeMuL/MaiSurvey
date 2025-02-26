<?php

namespace App\Presentation\Web\Response\Model\SurveyStatItem\Factory;

use App\Domain\Dto\SurveyStatItem\ChoiceCount as DomainChoiceCount;
use App\Domain\Dto\SurveyStatItem\RatingCount as DomainRatingCount;
use App\Domain\Dto\SurveyStatItem\StatDataInterface as DomainStatDataInterface;
use App\Domain\Enum\SurveyItemTypeEnum;
use App\Presentation\Web\Response\Model\SurveyStatItem\ChoiceCount;
use App\Presentation\Web\Response\Model\SurveyStatItem\ChoiceStatData;
use App\Presentation\Web\Response\Model\SurveyStatItem\CommentStatData;
use App\Presentation\Web\Response\Model\SurveyStatItem\MultiChoiceStatData;
use App\Presentation\Web\Response\Model\SurveyStatItem\RatingCount;
use App\Presentation\Web\Response\Model\SurveyStatItem\RatingStatData;
use App\Presentation\Web\Response\Model\SurveyStatItem\StatDataInterface;

class SurveyStatDataFactory
{
    public static function fromItemData(DomainStatDataInterface $data): StatDataInterface
    {
        return match ($data->getType()) {
            SurveyItemTypeEnum::Rating => new RatingStatData(
                $data->getType()->value,
                $data->getTeacherId()?->toRfc4122(),
                $data->getTeacherName(),
                $data->getCompletedCount(),
                $data->getAvailableCount(),
                array_map(
                    fn(DomainRatingCount $r) => new RatingCount(
                        $r->getCount(),
                        $r->getRating(),
                    ),
                    $data->getCounts(),
                ),
                round($data->getAverage(), 2),
            ),
            SurveyItemTypeEnum::Choice => new ChoiceStatData(
                $data->getType()->value,
                $data->getTeacherId()?->toRfc4122(),
                $data->getTeacherName(),
                $data->getCompletedCount(),
                $data->getAvailableCount(),
                array_map(
                    fn(DomainChoiceCount $r) => new ChoiceCount(
                        $r->getCount(),
                        $r->getChoice(),
                    ),
                    $data->getCounts(),
                ),
            ),
            SurveyItemTypeEnum::MultiChoice => new MultiChoiceStatData(
                $data->getType()->value,
                $data->getTeacherId()?->toRfc4122(),
                $data->getTeacherName(),
                $data->getCompletedCount(),
                $data->getAvailableCount(),
                array_map(
                    fn(DomainChoiceCount $r) => new ChoiceCount(
                        $r->getCount(),
                        $r->getChoice(),
                    ),
                    $data->getCounts(),
                ),
            ),
            SurveyItemTypeEnum::Comment => new CommentStatData(
                $data->getType()->value,
                $data->getTeacherId()?->toRfc4122(),
                $data->getTeacherName(),
                $data->getCompletedCount(),
                $data->getAvailableCount(),
                $data->getSummary(),
            ),
        };
    }
}
