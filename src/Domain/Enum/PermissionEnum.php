<?php

namespace App\Domain\Enum;

enum PermissionEnum: string
{
    // Survey
    case SurveyView = 'survey.view';
    case SurveyViewMy = 'survey.view_my';
    case SurveyComplete = 'survey.complete';
    case SurveyCreate = 'survey.create';
    case SurveyUpdate = 'survey.update';
    case SurveyDelete = 'survey.delete';
    case SurveyViewResult = 'survey.view_result';
    case SurveyViewAll = 'survey.view_all';

    // User
    case UserCreate = 'user.create';
    case UserView = 'user.view';
    case UserExport = 'user.export';
    case UserUpdateAll = 'user.update_all';

    // User Data
    case UserDataImport = 'user_data.import';
    case UserDataViewAll = 'user_data.view_all';

    // Group
    case GroupCreate = 'group.create';
    case GroupImport = 'group.import';

    // Subject
    case SubjectCreate = 'subject.create';
    case SubjectImport = 'subject.import';

    // User Subject
    case UserSubjectViewAll = 'user_subject.view_all';
    case UserSubjectViewMy = 'user_subject.view_my';
    case UserSubjectImport = 'user_subject.import';

    // Role
    case RoleViewAll = 'role.view_all';
}
