<?php

namespace App\Domain\Enum;

enum SurveyItemTypeEnum: string
{
    case Choice = 'choice';
    case MultiChoice = 'multi_choice';
    case Comment = 'comment';
    case Rating = 'rating';

    public function getName(): string
    {
        return match ($this) {
            self::Choice => 'Одиночный выбор',
            self::MultiChoice => 'Множественный выбор',
            self::Comment => 'Комментарий',
            self::Rating => 'Рейтинг',
        };
    }
}
