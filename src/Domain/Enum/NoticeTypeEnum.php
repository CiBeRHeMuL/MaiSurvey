<?php

namespace App\Domain\Enum;

enum NoticeTypeEnum: string
{
    case NewSurvey = 'new_survey';

    public function getName(): string
    {
        return match ($this) {
            self::NewSurvey => 'Новые опросы',
        };
    }
}

