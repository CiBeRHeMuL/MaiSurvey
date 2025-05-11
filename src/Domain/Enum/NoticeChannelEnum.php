<?php

namespace App\Domain\Enum;

enum NoticeChannelEnum: string
{
    case Telegram = 'telegram';

    public function getName(): string
    {
        return match ($this) {
            self::Telegram => 'Telegram',
        };
    }
}

