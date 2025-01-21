<?php

namespace App\Application\Mapper\Survey;

use App\Application\Dto\Survey\Create as Root;
use App\Domain\Dto\SurveyItem as Dest;
use App\Domain\Enum\SurveyItemTypeEnum;

class ItemDataMapper
{
    public static function map(Root\ItemDataInterface $data): Dest\ItemDataInterface
    {
        return match ($data->getType()) {
            SurveyItemTypeEnum::Choice->value => new Dest\ChoiceItemData(
                SurveyItemTypeEnum::Choice,
                array_map(self::mapChoice(...), $data->choices),
            ),
            SurveyItemTypeEnum::MultiChoice->value => new Dest\MultiChoiceItemData(
                SurveyItemTypeEnum::MultiChoice,
                array_map(self::mapChoice(...), $data->choices),
            ),
            SurveyItemTypeEnum::Comment->value => new Dest\CommentItemData(
                SurveyItemTypeEnum::Comment,
                $data->placeholder,
                $data->max_length,
            ),
            SurveyItemTypeEnum::Rating->value => new Dest\RatingItemData(
                SurveyItemTypeEnum::Rating,
                $data->min,
                $data->max,
            ),
        };
    }

    public static function mapChoice(Root\Choice $choice): Dest\Choice
    {
        return new Dest\Choice(
            $choice->text,
            $choice->value,
        );
    }
}
