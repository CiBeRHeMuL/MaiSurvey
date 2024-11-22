<?php

namespace App\Domain\Enum;

enum UserStatusEnum: string
{
    case Draft = 'draft';
    case Active = 'active';
}
