<?php

namespace App\Domain\Enum;

enum UserStatusEnum: string
{
    case Draft = 'draft';
    case Active = 'active';

    /**
     * @return UserStatusEnum[]
     */
    public function getAvailableStatuses(): array
    {
        return match ($this) {
            self::Draft => [self::Active],
            self::Active => [],
        };
    }
}
