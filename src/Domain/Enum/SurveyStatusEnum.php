<?php

namespace App\Domain\Enum;

enum SurveyStatusEnum: string
{
    case Active = 'active';
    case Draft = 'draft';
    case Closed = 'closed';

    public function getName(): string
    {
        return match ($this) {
            self::Active => 'Активен',
            self::Draft => 'Черновик',
            self::Closed => 'Завершен',
        };
    }
}
