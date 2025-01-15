<?php

namespace App\Domain\Enum;

enum RoleEnum: string
{
    case Admin = 'admin';
    case Student = 'student';
    case Teacher = 'teacher';
    case SurveyCreator = 'survey_creator';

    public function getName(): string
    {
        return match ($this) {
            self::Admin => 'Администратор',
            self::Student => 'Студент',
            self::Teacher => 'Преподаватель',
            self::SurveyCreator => 'Создатель опросов',
        };
    }

    /**
     * Доступы роли
     * @return PermissionEnum[]
     */
    public function getPermissions(): array
    {
        return match ($this) {
            self::Admin => [
                PermissionEnum::SurveyView,
                PermissionEnum::SurveyCreate,
                PermissionEnum::SurveyUpdate,
                PermissionEnum::SurveyDelete,
                PermissionEnum::SurveyViewResult,
                PermissionEnum::SurveyViewAll,
                PermissionEnum::UserCreate,
                PermissionEnum::UserView,
                PermissionEnum::UserExport,
                PermissionEnum::UserUpdateAll,
                PermissionEnum::UserDataImport,
                PermissionEnum::UserDataViewAll,
                PermissionEnum::GroupCreate,
                PermissionEnum::GroupImport,
                PermissionEnum::SubjectCreate,
                PermissionEnum::SubjectImport,
                PermissionEnum::StudentSubjectViewAll,
                PermissionEnum::RoleViewAll,
                PermissionEnum::TeacherSubjectViewAll,
                PermissionEnum::TeacherSubjectImport,
                PermissionEnum::StudentSubjectImport,
            ],
            self::Student => [
                PermissionEnum::SurveyComplete,
                PermissionEnum::SurveyView,
                PermissionEnum::SurveyViewResult,
                PermissionEnum::StudentSubjectViewMy,
                PermissionEnum::SurveyViewMy,
            ],
            self::Teacher => [
                PermissionEnum::TeacherSubjectViewMy,
            ],
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

    /**
     * Можно ли импортировать данные для пользователей для роли
     *
     * @return bool
     */
    public function importEnabled(): bool
    {
        return match ($this) {
            self::Student,
            self::Teacher => true,
            default => false,
        };
    }

    /**
     * Требуется ли роли группа
     *
     * @return bool
     */
    public function requiresGroup(): bool
    {
        return match ($this) {
            self::Student => true,
            default => false,
        };
    }
}
