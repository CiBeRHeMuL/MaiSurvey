<?php

namespace App\Domain\Enum;

enum PermissionEnum: string
{
    // Survey
    case SurveyView = 'survey.view';
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

    // User Data
    case UserDataImport = 'user_data.import';

    // Group
    case GroupCreate = 'group.create';
    case GroupImport = 'group.import';

    // Subject
    case SubjectCreate = 'subject.create';
    case SubjectImport = 'subject.import';

    // User Subject
    case UserSubjectViewAll = 'user_subject.view_all';
    case UserSubjectViewMy = 'user_subject.view_my';
}
