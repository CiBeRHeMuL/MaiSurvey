<?php

namespace App\Domain\Enum;

enum SurveyItemTypeEnum: string
{
    case Choice = 'choice';
    case MultiChoice = 'multi_choice';
    case Comment = 'comment';
    case Rating = 'rating';
}
