<?php

namespace App\Domain\Enum;

enum RoleEnum: string
{
    case Admin = 'admin';
    case Student = 'student';
    case Teacher = 'teacher';
    case SurveyCreator = 'survey_creator';

    /**
     * Доступы роли
     * @return PermissionEnum[]
     */
    public function getPermissions(): array
    {
        return match ($this) {
            self::Admin => PermissionEnum::cases(),
            self::Student => [
                PermissionEnum::SurveyComplete,
                PermissionEnum::SurveyView,
                PermissionEnum::SurveyViewResult,
            ],
            self::Teacher => [],
            self::SurveyCreator => [
                PermissionEnum::SurveyCreate,
                PermissionEnum::SurveyUpdate,
                PermissionEnum::SurveyDelete,
                PermissionEnum::SurveyViewAll,
            ],
        };
    }

    public function isMain(): bool
    {
        return match ($this) {
            self::Admin,
            self::Student,
            self::Teacher => true,
            default => false,
        };
    }
}
