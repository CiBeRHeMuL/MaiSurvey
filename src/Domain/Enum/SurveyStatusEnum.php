<?php

namespace App\Domain\Enum;

enum SurveyStatusEnum: string
{
    case Active = 'active';
    case Draft = 'draft';
    case Closed = 'closed';
}
