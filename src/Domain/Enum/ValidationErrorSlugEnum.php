<?php

namespace App\Domain\Enum;

enum ValidationErrorSlugEnum: string
{
    case LandingNotFound = 'LANDING_NOT_FOUND';
    case AlreadyExists = 'ALREADY_EXISTS';
    case NotFound = 'NOT_FOUND';
    case WrongField = 'WRONG_FIELD';
    case FileNotExists = 'FILE_NOT_EXISTS';
    case WrongFile = 'WRONG_FILE';

    public function getSlug(): string
    {
        return $this->value;
    }
}
