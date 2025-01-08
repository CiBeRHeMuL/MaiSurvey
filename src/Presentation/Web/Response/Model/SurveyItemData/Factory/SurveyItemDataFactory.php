<?php

namespace App\Presentation\Web\Response\Model\SurveyItemData\Factory;

use App\Domain\Dto\SurveyItem\ItemDataInterface as DomainItemDataInterfaceAlias;
use App\Domain\Enum\SurveyItemTypeEnum;
use App\Presentation\Web\Response\Model\Choice;
use App\Presentation\Web\Response\Model\SurveyItemData\ChoiceItemData;
use App\Presentation\Web\Response\Model\SurveyItemData\CommentItemData;
use App\Presentation\Web\Response\Model\SurveyItemData\ItemDataInterface;
use App\Presentation\Web\Response\Model\SurveyItemData\MultiChoiceItemData;
use App\Presentation\Web\Response\Model\SurveyItemData\RatingItemData;

class SurveyItemDataFactory
{
    public static function fromItemData(DomainItemDataInterfaceAlias $data): ItemDataInterface
    {
        return match ($data->getType()) {
            SurveyItemTypeEnum::Choice => new ChoiceItemData(
                $data->getType()->value,
                array_map(
                    Choice::fromChoice(...),
                    $data->getChoices(),
                ),
            ),
            SurveyItemTypeEnum::MultiChoice => new MultiChoiceItemData(
                $data->getType()->value,
                array_map(
                    Choice::fromChoice(...),
                    $data->getChoices(),
                ),
            ),
            SurveyItemTypeEnum::Comment => new CommentItemData(
                $data->getType()->value,
                $data->getPlaceholder(),
            ),
            SurveyItemTypeEnum::Rating => new RatingItemData(
                $data->getType()->value,
                $data->getRatings(),
            ),
        };
    }
}
