<?php

namespace App\Presentation\Web\Response\Model\SurveyStatItem\Factory;

use App\Domain\Dto\SurveyItem\Choice;
use App\Domain\Dto\SurveyStatItem\ChoiceCount as DomainChoiceCount;
use App\Domain\Dto\SurveyStatItem\RatingCount as DomainRatingCount;
use App\Domain\Dto\SurveyStatItem\StatDataInterface as DomainStatDataInterface;
use App\Domain\Entity\SurveyStatItem;
use App\Domain\Enum\SurveyItemTypeEnum;
use App\Domain\Helper\HArray;
use App\Presentation\Web\Response\Model\SurveyStatItem\ChoiceCount;
use App\Presentation\Web\Response\Model\SurveyStatItem\ChoiceStatData;
use App\Presentation\Web\Response\Model\SurveyStatItem\CommentStatData;
use App\Presentation\Web\Response\Model\SurveyStatItem\MultiChoiceStatData;
use App\Presentation\Web\Response\Model\SurveyStatItem\RatingCount;
use App\Presentation\Web\Response\Model\SurveyStatItem\RatingStatData;
use App\Presentation\Web\Response\Model\SurveyStatItem\StatDataInterface;
use InvalidArgumentException;

class SurveyStatDataFactory
{
    public static function fromItemData(DomainStatDataInterface $data, SurveyStatItem $item): StatDataInterface
    {
        switch ($data->getType()) {
            case SurveyItemTypeEnum::Rating:
                return new RatingStatData(
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
                );
            case SurveyItemTypeEnum::Choice:
                /** @var Choice[] $choices */
                $choices = HArray::index(
                    $item->getItem()->getData()->getChoices(),
                    fn(Choice $c) => $c->getValue(),
                );
                return new ChoiceStatData(
                    $data->getType()->value,
                    $data->getTeacherId()?->toRfc4122(),
                    $data->getTeacherName(),
                    $data->getCompletedCount(),
                    $data->getAvailableCount(),
                    array_map(
                        fn(DomainChoiceCount $r) => new ChoiceCount(
                            $r->getCount(),
                            $choices[$r->getChoice()]->getText(),
                        ),
                        $data->getCounts(),
                    ),
                );
            case SurveyItemTypeEnum::MultiChoice:
                /** @var Choice[] $choices */
                $choices = HArray::index(
                    $item->getItem()->getData()->getChoices(),
                    fn(Choice $c) => $c->getValue(),
                );
                return new MultiChoiceStatData(
                    $data->getType()->value,
                    $data->getTeacherId()?->toRfc4122(),
                    $data->getTeacherName(),
                    $data->getCompletedCount(),
                    $data->getAvailableCount(),
                    array_map(
                        fn(DomainChoiceCount $r) => new ChoiceCount(
                            $r->getCount(),
                            $choices[$r->getChoice()]->getText(),
                        ),
                        $data->getCounts(),
                    ),
                );
            case SurveyItemTypeEnum::Comment:
                return new CommentStatData(
                    $data->getType()->value,
                    $data->getTeacherId()?->toRfc4122(),
                    $data->getTeacherName(),
                    $data->getCompletedCount(),
                    $data->getAvailableCount(),
                    $data->getSummary(),
                );
        }
    }
}
